<?php

/**
 * A neat little wrapper
 * for instantiation of 
 * ImageCop class.
 *
 * @param array $options
 * @return ImageCop
 */
function image_cop( array $options = null ){
    $imageCop = new ImageCop($options);
    error_log($imageCop->bucket);
    return $imageCop;
}