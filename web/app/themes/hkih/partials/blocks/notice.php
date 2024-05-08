<?php
/**
 * Notice block partial required fields.
 *
 * @var array $fields Fields data array.
 */
$fields = wp_parse_args( $fields['data'], [
    'title'       => false,
    'description' => false,
    'icon'        => false,
] );

if ( empty( $fields['title'] ) && empty( $fields['description'] ) ) {
    return;
}
?>
<div class="notice">
    <div class="notice__inner">
        <?php if ( ! empty( $fields['title'] ) ) : ?>
            <h2 class="notice__title">
                <?php echo esc_html( $fields['title'] ); ?>
            </h2>
        <?php endif; ?>

        <?php if ( ! empty( $fields['description'] ) ) : ?>
            <div class="notice__description">
                <?php echo wp_kses_post( $fields['description'] ); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ( ! empty( $fields['icon'] ) ) : ?>
        <div class="notice__icon">
            <span class="hds-icon hds-icon--<?php echo esc_attr( $fields['icon'] ); ?>"></span>
        </div>
    <?php endif; ?>
</div>
