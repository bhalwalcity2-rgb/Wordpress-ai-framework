<?php
/**
 * "Rich Content" section.
 *
 * The long-form prose section used by location and service pages, where
 * the copy is genuinely page-specific rather than a city-name swap of
 * the homepage. Renders any combination of an intro, body paragraphs,
 * H3 sub-blocks, a reference table, and a closing footnote — so one
 * section template covers the whole spread of a written page.
 *
 * Copy arrives as plain prose and goes out through lvjcb_prose(), which
 * escapes it, links city mentions to their location pages, and turns the
 * phone number into a tel: link. Content files therefore never contain
 * markup or URLs.
 *
 * @param array $args {
 *     @type string $id         Optional unique identifier for this instance.
 *     @type string $eyebrow    Optional eyebrow label.
 *     @type string $heading    Section heading text.
 *     @type string $intro      Optional supporting paragraph.
 *     @type array  $paragraphs Optional list of body paragraphs.
 *     @type array  $blocks     Optional list of ['heading', 'paragraphs'].
 *     @type array  $table      Optional ['caption', 'columns', 'rows'].
 *     @type string $footnote   Optional closing paragraph.
 *     @type array  $image      Optional ['slug', 'alt', 'caption', 'align'].
 *                              align is 'left' or 'right' to sit beside the
 *                              prose, or 'wide' to run the container width.
 *     @type string $variant    Optional section background modifier, e.g. 'alt'.
 * }
 */

$heading    = $args['heading'] ?? '';
$intro      = $args['intro'] ?? '';
$paragraphs = $args['paragraphs'] ?? array();
$blocks     = $args['blocks'] ?? array();
$table      = $args['table'] ?? array();
$footnote   = $args['footnote'] ?? '';
$image      = $args['image'] ?? array();

if ( '' === $heading && ! $paragraphs && ! $blocks && ! $table ) {
	return;
}

$image_id = ! empty( $image['slug'] ) ? lvjcb_get_attachment_id_by_slug( $image['slug'] ) : 0;
$align    = $image['align'] ?? 'right';

/*
 * An image only sits beside the prose when there is prose to sit beside;
 * a section that is a heading and a table gets the full-width treatment
 * instead, so the image never ends up in a narrow column next to nothing
 * while the table it belongs to runs underneath it.
 */
$side_image = $image_id && 'wide' !== $align && ( $paragraphs || $blocks );
$wide_image = $image_id && ! $side_image;

$layout_classes = 'lvjcb-rich-content__layout';
if ( $side_image ) {
	$layout_classes .= ' lvjcb-rich-content__layout--split';
	if ( 'left' === $align ) {
		$layout_classes .= ' lvjcb-rich-content__layout--media-first';
	}
}

$section_id = sanitize_title( $args['id'] ?? '' );
if ( '' === $section_id ) {
	$section_id = wp_unique_id( 'rich-content-' );
}
$heading_id = $section_id . '-heading';

$eyebrow = $args['eyebrow'] ?? '';
$variant = $args['variant'] ?? '';

$classes = 'lvjcb-section lvjcb-rich-content';
if ( $variant ) {
	$classes .= ' lvjcb-section--' . sanitize_html_class( $variant );
}

$columns = $table['columns'] ?? array();
$rows    = $table['rows'] ?? array();
?>
<section class="<?php echo esc_attr( $classes ); ?>" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
	<div class="lvjcb-section__container">

		<?php if ( $eyebrow ) : ?>
			<p class="lvjcb-section__eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<?php endif; ?>

		<h2 id="<?php echo esc_attr( $heading_id ); ?>" class="lvjcb-section__heading">
			<?php echo esc_html( $heading ); ?>
		</h2>

		<?php if ( $intro ) : ?>
			<p class="lvjcb-section__intro"><?php echo lvjcb_prose( $intro ); ?></p>
		<?php endif; ?>

		<?php if ( $wide_image ) : ?>
			<?php get_template_part( 'template-parts/components/content-figure', null, array(
				'image_id' => $image_id,
				'alt'      => $image['alt'] ?? '',
				'caption'  => $image['caption'] ?? '',
				'modifier' => 'wide',
			) ); ?>
		<?php endif; ?>

		<?php if ( $paragraphs || $blocks ) : ?>
			<div class="<?php echo esc_attr( $layout_classes ); ?>">

				<div class="lvjcb-rich-content__prose">

					<?php if ( $paragraphs ) : ?>
						<div class="lvjcb-rich-content__body">
							<?php foreach ( $paragraphs as $paragraph ) : ?>
								<p><?php echo lvjcb_prose( $paragraph ); ?></p>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<?php if ( $blocks ) : ?>
						<div class="lvjcb-rich-content__blocks">
							<?php foreach ( $blocks as $block ) : ?>
								<div class="lvjcb-rich-content__block">
									<?php if ( ! empty( $block['heading'] ) ) : ?>
										<h3 class="lvjcb-rich-content__block-heading"><?php echo esc_html( $block['heading'] ); ?></h3>
									<?php endif; ?>
									<?php foreach ( $block['paragraphs'] ?? array() as $paragraph ) : ?>
										<p><?php echo lvjcb_prose( $paragraph ); ?></p>
									<?php endforeach; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

				</div>

				<?php if ( $side_image ) : ?>
					<?php get_template_part( 'template-parts/components/content-figure', null, array(
						'image_id' => $image_id,
						'alt'      => $image['alt'] ?? '',
						'caption'  => $image['caption'] ?? '',
					) ); ?>
				<?php endif; ?>

			</div>
		<?php endif; ?>

		<?php if ( $columns && $rows ) : ?>
			<?php /* The wrapper scrolls on its own so a wide table never makes the page scroll sideways. */ ?>
			<div class="lvjcb-rich-content__table-wrap" tabindex="0" role="region" aria-labelledby="<?php echo esc_attr( $heading_id ); ?>">
				<table class="lvjcb-rich-content__table">
					<?php if ( ! empty( $table['caption'] ) ) : ?>
						<caption class="lvjcb-rich-content__table-caption"><?php echo esc_html( $table['caption'] ); ?></caption>
					<?php endif; ?>
					<thead>
						<tr>
							<?php foreach ( $columns as $column ) : ?>
								<th scope="col"><?php echo esc_html( $column ); ?></th>
							<?php endforeach; ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $rows as $row ) : ?>
							<tr>
								<?php foreach ( $row as $index => $cell ) : ?>
									<?php
									/*
									 * A cell is either plain text or an array naming a
									 * service slug, which is resolved to a URL here so
									 * the content file never stores one.
									 */
									$is_link = is_array( $cell );
									$text    = $is_link ? ( $cell['text'] ?? '' ) : $cell;
									$tag     = 0 === $index ? 'th' : 'td';
									$scope   = 0 === $index ? ' scope="row"' : '';
									?>
									<<?php echo $tag . $scope; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
										<?php if ( $is_link && ! empty( $cell['service'] ) ) : ?>
											<a href="<?php echo esc_url( lvjcb_get_service_url( $cell['service'] ) ); ?>"><?php echo esc_html( $text ); ?></a>
										<?php else : ?>
											<?php echo esc_html( $text ); ?>
										<?php endif; ?>
									</<?php echo esc_html( $tag ); ?>>
								<?php endforeach; ?>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>

		<?php if ( $footnote ) : ?>
			<p class="lvjcb-rich-content__footnote"><?php echo lvjcb_prose( $footnote ); ?></p>
		<?php endif; ?>

	</div>
</section>
