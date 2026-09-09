"""Minimal JSON Schema validator — Python standard library only.

The framework may not take on a new dependency (PROJECT_CONTEXT.md §15,
AI_RULES.md §4), so `jsonschema` is not available. This implements the
subset of draft-07 the content schemas actually use, plus two documented
extensions, and reports failures with a precise path so the message names
the offending field rather than the whole file.

Supported: type, required, properties, additionalProperties (false),
items, minItems, minLength, enum, const, pattern, oneOf, anyOf, $ref
(same-file "#/definitions/x" and cross-file "other.json#/definitions/x").

Extensions:
  selectOn / select   Discriminated union. Standard draft-07 expresses
                      this as oneOf, but a failed oneOf can only report
                      "matched none of N schemas" — useless when a
                      section is one required field short. selectOn picks
                      the branch by discriminator value first, so the
                      error names the real problem. A standard validator
                      ignores unknown keywords, so a schema using this
                      still validates (more weakly) elsewhere rather than
                      erroring.
  x-deprecated        Marks a still-permitted key as superseded. Emits a
                      warning instead of an error, which is what lets a
                      contract be frozen before the content sweep that
                      removes the key.
"""

import json
import re
from pathlib import Path

ERROR = "error"
WARNING = "warning"

_TYPES = {
    "object": dict,
    "array": list,
    "string": str,
    "boolean": bool,
    "null": type(None),
}


class Issue:
    """One validation finding, anchored to a path inside the document."""

    def __init__(self, path, message, level=ERROR):
        self.path = path or "(root)"
        self.message = message
        self.level = level

    def __str__(self):
        return f"{self.path}: {self.message}"


def _type_name(value):
    if isinstance(value, bool):
        return "boolean"
    if isinstance(value, dict):
        return "object"
    if isinstance(value, list):
        return "array"
    if isinstance(value, str):
        return "string"
    if isinstance(value, (int, float)):
        return "number"
    if value is None:
        return "null"
    return type(value).__name__


def _matches_type(value, expected):
    if expected == "number":
        return isinstance(value, (int, float)) and not isinstance(value, bool)
    if expected == "integer":
        return isinstance(value, int) and not isinstance(value, bool)
    if expected == "boolean":
        return isinstance(value, bool)
    py = _TYPES.get(expected)
    if py is None:
        return True
    if py in (dict, list, str) and isinstance(value, bool):
        return False
    return isinstance(value, py)


class SchemaValidator:
    """Validates documents against schema files in a single directory."""

    def __init__(self, schema_dir):
        self.schema_dir = Path(schema_dir)
        self._cache = {}

    def load(self, filename):
        if filename not in self._cache:
            path = self.schema_dir / filename
            if not path.is_file():
                raise FileNotFoundError(f"Schema not found: {path}")
            self._cache[filename] = json.loads(path.read_text(encoding="utf-8"))
        return self._cache[filename]

    def _resolve(self, ref, current_file):
        """Resolve a $ref to (schema_node, defining_file)."""
        if "#" in ref:
            file_part, _, fragment = ref.partition("#")
        else:
            file_part, fragment = ref, ""

        target_file = file_part or current_file
        node = self.load(target_file)

        for token in [t for t in fragment.split("/") if t]:
            token = token.replace("~1", "/").replace("~0", "~")
            if not isinstance(node, dict) or token not in node:
                raise KeyError(f"Unresolvable $ref: {ref}")
            node = node[token]

        return node, target_file

    def validate(self, data, schema_file):
        """Validate a decoded document. Returns a list of Issue."""
        issues = []
        self._check(data, self.load(schema_file), "", schema_file, issues)
        return issues

    # -- internals ---------------------------------------------------

    def _check(self, value, schema, path, cur_file, issues):
        if not isinstance(schema, dict):
            return

        if "$ref" in schema:
            try:
                target, target_file = self._resolve(schema["$ref"], cur_file)
            except (KeyError, FileNotFoundError) as exc:
                issues.append(Issue(path, str(exc)))
                return
            # Descriptions on the referring node stay with the referrer;
            # everything else comes from the target.
            self._check(value, target, path, target_file, issues)
            return

        if "x-deprecated" in schema:
            issues.append(Issue(path, schema["x-deprecated"], WARNING))

        expected = schema.get("type")
        if expected and not _matches_type(value, expected):
            issues.append(
                Issue(path, f"expected {expected}, found {_type_name(value)}")
            )
            return

        if "const" in schema and value != schema["const"]:
            issues.append(
                Issue(path, f"must be {schema['const']!r}, found {value!r}")
            )

        if "enum" in schema and value not in schema["enum"]:
            allowed = ", ".join(repr(v) for v in schema["enum"])
            issues.append(
                Issue(path, f"{value!r} is not one of: {allowed}")
            )

        if isinstance(value, str):
            self._check_string(value, schema, path, issues)
        if isinstance(value, list):
            self._check_array(value, schema, path, cur_file, issues)
        if isinstance(value, dict):
            self._check_object(value, schema, path, cur_file, issues)

        if "oneOf" in schema:
            self._check_branches(value, schema["oneOf"], "oneOf", path, cur_file, issues)
        if "anyOf" in schema:
            self._check_branches(value, schema["anyOf"], "anyOf", path, cur_file, issues)

    def _check_string(self, value, schema, path, issues):
        minimum = schema.get("minLength")
        if minimum is not None and len(value) < minimum:
            if minimum == 1:
                issues.append(Issue(path, "must not be empty"))
            else:
                issues.append(
                    Issue(path, f"must be at least {minimum} characters, is {len(value)}")
                )

        pattern = schema.get("pattern")
        if pattern and not re.match(pattern, value):
            issues.append(Issue(path, f"{value!r} does not match {pattern}"))

    def _check_array(self, value, schema, path, cur_file, issues):
        minimum = schema.get("minItems")
        if minimum is not None and len(value) < minimum:
            issues.append(
                Issue(path, f"must contain at least {minimum} item(s), has {len(value)}")
            )

        item_schema = schema.get("items")
        if item_schema:
            for index, item in enumerate(value):
                self._check(item, item_schema, f"{path}[{index}]", cur_file, issues)

    def _check_object(self, value, schema, path, cur_file, issues):
        for key in schema.get("required", []):
            if key not in value:
                issues.append(Issue(path, f"missing required property '{key}'"))

        properties = schema.get("properties", {})
        for key, sub_schema in properties.items():
            if key in value:
                child = f"{path}.{key}" if path else key
                self._check(value[key], sub_schema, child, cur_file, issues)

        if schema.get("additionalProperties") is False:
            for key in value:
                if key in properties:
                    continue
                child = f"{path}.{key}" if path else key
                issues.append(Issue(child, self._unknown_property_message(key)))

        # Discriminated union — see module docstring.
        discriminator = schema.get("selectOn")
        if discriminator and discriminator in value:
            branch = schema.get("select", {}).get(value[discriminator])
            if branch:
                self._check(value, branch, path, cur_file, issues)

    @staticmethod
    def _unknown_property_message(key):
        if key == "body":
            return (
                "unknown property 'body' — this is the superseded v1 shape. "
                "Prose belongs in 'paragraphs' as a list. A 'body' string reaches "
                "no renderer and the section publishes as an empty heading (ADR-0003)"
            )
        return f"unknown property '{key}'"

    def _check_branches(self, value, branches, keyword, path, cur_file, issues):
        for branch in branches:
            if not self._collect(value, branch, cur_file):
                return  # one branch passed

        # Nothing matched. Say what was actually wanted.
        wanted = [b.get("required") for b in branches if b.get("required")]
        if wanted and all(len(w) == 1 for w in wanted):
            names = ", ".join(w[0] for w in wanted)
            issues.append(Issue(path, f"must contain at least one of: {names}"))
            return

        types = [b.get("type") for b in branches if b.get("type")]
        if len(types) == len(branches):
            issues.append(
                Issue(path, f"must be one of: {', '.join(types)} — found {_type_name(value)}")
            )
            return

        issues.append(Issue(path, f"does not satisfy any permitted {keyword} form"))

    def _collect(self, value, schema, cur_file):
        """Validate against one branch in isolation; return its errors."""
        probe = []
        self._check(value, schema, "", cur_file, probe)
        return [i for i in probe if i.level == ERROR]
