<?php

/**
 * Convert a hexa decimal color code to its RGB equivalent
 *
 * @param string $hexStr (hexadecimal color value)
 * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
 * @param string $seperator (to separate RGB values. Applicable only if second parameter is true.)
 * @return array or string (depending on second parameter. Returns False if invalid hex color value)
 */
if ( ! function_exists( 'slickity_hex2RGB' ) ) {
  function slickity_hex2RGB( $hexStr, $returnAsString = false, $seperator = ',' ) {
    // Gets a proper hex string
    $hexStr = preg_replace( "/[^0-9A-Fa-f]/", '', $hexStr );

    $rgbArray = array();

    if ( strlen( $hexStr ) == 6 ) {
      // If a proper hex code, convert using bitwise operation. No overhead... faster!
      $colorVal = hexdec( $hexStr );
      $rgbArray['red'] = 0xFF & ( $colorVal >> 0x10 );
      $rgbArray['green'] = 0xFF & ( $colorVal >> 0x8 );
      $rgbArray['blue'] = 0xFF & $colorVal;
    } elseif ( strlen( $hexStr ) == 3 ) {
      // If shorthand notation, need some string manipulations
      $rgbArray['red'] = hexdec( str_repeat( substr( $hexStr, 0, 1 ), 2) );
      $rgbArray['green'] = hexdec( str_repeat( substr( $hexStr, 1, 1 ), 2) );
      $rgbArray['blue'] = hexdec( str_repeat( substr( $hexStr, 2, 1 ), 2) );
    } else {
      // Invalid hex color code
      return false;
    }

    // Returns the rgb string or the associative array
    return $returnAsString ? implode( $seperator, $rgbArray ) : $rgbArray;
  }
}

/**
 * Geneate the HTML for a slideshow
 *
 * @param  int $slideshowID ID for the slideshow to generate HTML for
 * @param  array $slides Array of slides from Advanced Custom Fields
 * @param  string $type Type of slideshow to generate (main, thumbnail, lightbox)
 * @param  boolean $hasThumbnail True if slideshow has thumbnail slideshow
 * @param  boolean $hasLightbox True if slideshow has lightbox slideshow
 * @return array or false Array with slideshow ID & HTML or false on error
 */
if ( ! function_exists( 'slickity_slideshow' ) ) {
  function slickity_slideshow( $slideshowID, $slides, $type, $hasThumbnail, $hasLightbox ) {
    // Validate the type
    if ( ! in_array( $type, array( 'main', 'thumbnail', 'lightbox' ) ) ) {
      return false;
    }

    // Get the slide settings
    $settings = get_field( 'slickity_' . $type . '_settings' );
    if ( ! $settings ) return false;

    // Prepare the slideshow container classes
    $container_classes = 'slickity slickity--' . $type;

    // Check if a template was selected for the container
    if ( isset( $settings['template'] ) && $settings['template'] ) {
      $container_classes .= ' slickity-' . $type . '-template--' . $settings['template'];
    }

    // Check if a custom CSS class has been set for the container
    if ( isset( $settings['css'] ) && $settings['css'] ) {
      $container_classes .= ' ' . $settings['css'];
    }

    // Create the HTML container for the slideshow
    $html = '';

    // Add container around slideshow if lightbox
    if ( 'lightbox' === $type ) {
      $html .= '<div class="slickity-lightbox-container" id="slickity-lightbox-container-' . $slideshowID . '">'; // Open lightbox container
    }

    $html .= '<div class="' . $container_classes . '" id="slickity-' . $type . '-' . $slideshowID . '">';

    // Loop through and create the slides
    foreach( $slides as $key => $slide ):
      $html .= slickity_slide( $slide, $key, $type, $slideshowID, $hasLightbox );
    endforeach;

    $html .= '</div>';

    if ( 'lightbox' === $type ) {
      $html .= '</div>'; // Close lightbox container
    }

    // Check if additional slideshows need to be synced
    $asNavFor = '';

    // Check for any slideshows that need to be syned
    if ( 'main' === $type ) {

      // Check if should be synced with a thumbnail slideshow
      if ( $hasThumbnail ) {
        if ( $asNavFor ) $asNavFor .= ', ';
        $asNavFor .= '#slickity-thumbnail-' . $slideshowID;
      }

      // Check if should be synced with a lightbox slideshow
      if ( $hasLightbox ) {
        if ( $asNavFor ) $asNavFor .= ', ';
        $asNavFor .= '#slickity-lightbox-' . $slideshowID;
      }
    } elseif( 'thumbnail' === $type || 'lightbox' === $type ) {

      // Link main slideshow
      if ( $asNavFor ) $asNavFor .= ', ';
      $asNavFor .= '#slickity-main-' . $slideshowID;

      if ( 'thumbnail' === $type ) {

        // Check if should be synced with a lightbox slideshow
        if ( $hasLightbox ) {
          if ( $asNavFor ) $asNavFor .= ', ';
          $asNavFor .= '#slickity-lightbox-' . $slideshowID;
        }
      } else {
        // Check if should be synced with a thumbnail slideshow
        if ( $hasThumbnail ) {
          if ( $asNavFor ) $asNavFor .= ', ';
          $asNavFor .= '#slickity-thumbnail-' . $slideshowID;
        }
      }
    }

    // Generate the JS for the slideshow
    $html .= '<script>';
    $html .= 'jQuery(function( $ ) {'; // jQuery container opening
    $html .= "$( '#slickity-" . $type . "-" . $slideshowID . "' ).slick({"; // Slick opening

    // Check if asNavFor is set
    if ( $asNavFor ) {
      $html .= "asNavFor: '" . $asNavFor . "',";
    }

    // Check if responsive options are set
    if (
      isset( $settings['responsive'] ) &&
      $settings['responsive'] &&
      count( $settings['responsive_options'] )
    ) {
      $html .= 'responsive: ['; // Responsive options opening

      // Loop through the responsive options
      foreach( $settings['responsive_options'] as $key => $ary ) {

        // Format the responsive settings
        $responsive_settings = slickity_settings( $ary['settings'], true );

        $html .= '{ breakpoint: ' . $ary['breakpoint'] . ',' . 'settings: ' . $responsive_settings;
      }

      $html .= '],'; // Responsive options closing
    }

    // Add the rest of the settings
    $html .= slickity_settings( $settings );

    $html .= '});'; // Slick closing
    $html .= '});'; // jQuery container closing
    $html .= '</script>';

    return $html;
  }
}

/**
 * Generate the HTML for a slide template
 *
 * @param  string $template HTML template (default)
 * @param  array $template_options Array of template options
 * @param  string $type Type of slide (main, thumbnail, lightbox)
 * @return string HTML for the slide template
 */
if ( ! function_exists( 'slickity_template' ) ) {
  function slickity_template( $template, $template_options, $type ) {
    $html = '';

    switch( $template ) {
      case 'default':

        // Default template
        switch( $type ) {
          case 'main':

            // Image
            $image_size = isset( $template_options['image_size'] ) ? $template_options['image_size'] : 'full';

            $html .= '<div class="slickity-slide__image">';
            $html .= wp_get_attachment_image( $template_options['image']['ID'], $image_size );
            $html .= '</div>';

            // Caption
            if ( $template_options['caption'] ) {
              $rgb = slickity_hex2RGB( $template_options['bg_color'] );
              $rgba = $rgb['red'] . ',' . $rgb['green'] . ',' . $rgb['blue'] . ',' . $template_options['bg_opacity'];

              $html .= '<div class="slickity-slide__caption" style="color: ' . $template_options['text_color'] . '; background-color: rgba(' . $rgba . ');">';
              $html .= apply_filters( 'the_content', $template_options['caption'] );
              $html .= '</div>';
            }

            break;
          case 'thumbnail':

            // Image
            $image_size = isset( $template_options['thumbnail_image_size'] ) ? $template_options['thumbnail_image_size'] : 'full';

            $html .= '<div class="slickity-slide__image">';
            $html .= wp_get_attachment_image( $template_options['image']['ID'], $image_size );
            $html .= '</div>';

            break;
          case 'lightbox':

            // Image
            $image_size = isset( $template_options['lightbox_image_size'] ) ? $template_options['lightbox_image_size'] : 'full';

            $html .= '<div class="slickity-slide__image">';
            $html .= wp_get_attachment_image( $template_options['image']['ID'], $image_size );
            $html .= '</div>';

            // Caption
            if ( $template_options['caption'] ) {
              $rgb = slickity_hex2RGB( $template_options['bg_color'] );
              $rgba = $rgb['red'] . ',' . $rgb['green'] . ',' . $rgb['blue'] . ',' . $template_options['bg_opacity'];

              $html .= '<div class="slickity-slide__caption" style="color: ' . $template_options['text_color'] . '; background-color: rgba(' . $rgba . ');">';
              $html .= apply_filters( 'the_content', $template_options['caption'] );
              $html .= '</div>';
            }
            break;
        }
        break;
    }

    return $html;
  }
}

/**
 * Generate the HTML for custom slide content
 *
 * @param  string $type Type of slide (main, thumbnail, lightbox)
 * @param  array $args Array for the main, thumbnail & lightbox content
 * @return string HTML for the slide
 */
if ( ! function_exists( 'slickity_content' ) ) {
  function slickity_content( $type, $args ) {
    $html = '';

    switch( $type ) {
      case 'main':
        $html .= $args['content'];
        break;
      case 'thumbnail':
        if ( $args['thumbnail'] ) {
          $html .= $args['thumbnail'];
        } else {
          $html .= $args['content'];
        }
        break;
      case 'lightbox':
        if ( $args['lightbox'] ) {
          $html .= $args['lightbox'];
        } else {
          $html .= $args['content'];
        }
        break;
    }

    return $html;
  }
}

/**
 * Determines if a time is within a range.
 *
 * @param  int $start Start time
 * @param  int $end End time
 * @param  int $time Time to check
 * @return boolean True if time is within range
 */
if ( ! function_exists( 'slickity_time_range' ) ) {
  function slickity_time_range( $start, $end, $time = false ) {
    $current = new DateTime();
    $time = ( $time ? $current->setTimestamp( strtotime( $time ) ) : $current->setTimestamp( current_time( 'timestamp' ) ) );

    list( $startHour, $startMin, $startSec ) = explode( ':', $start );
    list( $endHour, $endMin, $endSec ) = explode( ':', $end );

    $start = new DateTime();
    $end = new DateTime();

    $start->setTime( $startHour, $startMin, $startSec );
    $end->setTime( $endHour, $endMin, $endSec );

    if ( ( $time->getTimestamp() >= $start->getTimestamp() ) && ( $time->getTimestamp() <= $end->getTimestamp() ) ) {
      return true;
    }

    return false;
  }
}

/**
 * Generate the HTML for a slide
 *
 * @param  array $slide Slide settings
 * @param  int $num Number of the slide
 * @param  string $type Type of slide (main, thumbnail, lightbox)
 * @param  int $slideshowID ID of the slideshow
 * @return string or boolean HTML for the slide or false if shouldn't be displayed
 */
if ( ! function_exists( 'slickity_slide' ) ) {
  function slickity_slide( $slide, $num, $type, $slideshowID, $hasLightbox = false ) {
    // Check if a schedule has been set
    if ( $slide['schedule'] ) {
      // Check year
      if ( $slide['year'] && ! in_array( date( 'Y' ), $slide['year'] ) ) return false;

      // Check month
      if ( $slide['month'] && ! in_array( date( 'n' ), $slide['month'] ) ) return false;

      // Check date
      if ( $slide['date'] && ! in_array( date( 'j' ), $slide['date'] ) ) return false;

      // Check day
      if ( $slide['day_of_week'] && ! in_array( date( 'N' ), $slide['day_of_week'] ) ) return false;

      // Check time
      if ( $slide['time'] ) {
        $show = false;

        foreach( $slide['time'] as $key => $ary ) {
          if ( slickity_time_range( $ary['start'], $ary['end'] ) ) $show = true;
          if ( $show ) break;
        }

        if ( ! $show ) return false;
      }

      // Check user
      if ( $slide['user'] && ! in_array( get_current_user_id(), $slide['user'] ) ) return false;
    }

    // Prepare the slide container classes
    $slide_css = 'slickity-slide slickity-slide-' . $type;

    // Check if a template was selected
    $template = false;
    if ( $slide['template'] ) {
      $template = $slide['templates']['template'];
      $template_options = $slide['templates'][ $template ];

      $slide_css .= ' slickity-slide-' . $type . '--' . $template;
    }

    // Set main slideshow classes
    if ( 'main' === $type ) {

      // Check if custom CSS class set
      if ( $slide['css'] ) {
        $slide_css .= ' ' . $slide['css'];
      }

      // Add lighbox trigger class if needed
      if ( $hasLightbox ) {
        $slide_css .= ' slickity-lightbox';
      }
    }

    // Check if a custom class is set for thumbnail
    if ( 'thumbnail' === $type && $slide['thumbnail_css'] ) {
      $slide_css .= ' ' . $slide['thumbnail_css'];
    }

    // Check if a custom class is set for lightbox
    if ( 'lightbox' === $type && $slide['lightbox_css'] ) {
      $slide_css .= ' ' . $slide['lightbox_css'];
    }

    $html = '<div class="' . $slide_css . '" id="slickity-slide-' . $type . '-' . $slideshowID . '-' . $num . '" data-id="' . $slideshowID . '">'; // Slide opening

    // Check for template output
    if ( $template ) {
      $html .= slickity_template( $template, $template_options, $type );
    } else {
      $args = array(
        'content'   => $slide['slide_content'],
        'thumbnail' => ( $slide['thumbnail'] ? $slide['thumbnail_content'] : false ),
        'lightbox'  => ( $slide['lightbox'] ? $slide['lightbox_content'] : false )
      );
      $html .= slickity_content( $type, $args );
    }

    $html .= '</div>'; // Slide closing

    return $html;
  }
}

/**
 * Geneate the HTML for a slideshow
 *
 * @param  array $settings Array of slick settings
 * @param  boolean $isResponsive True is provided settings array comes from responsive settings field
 * @return string JS settings for slick
 */
if ( ! function_exists( 'slickity_settings' ) ) {
  function slickity_settings( $settings, $isResponsive = false ) {

    // Check if the provided settings are responsive so they can be converted
    if ( $isResponsive ) {
      $responsive_array = array();

      foreach( $settings as $key => $ary ) {
        $responsive_array[ $ary['setting'] ] = $ary['value'];
      }

      $settings = $responsive_array;
    }

    // Check if additional settings are provided so they can be converted
    if ( isset( $settings['additional_settings'] ) && $settings['additional_settings'] ) {
      foreach( $settings['additional_settings'] as $key => $ary ) {
        $settings[ $ary['setting'] ] = $ary['value'];
      }
    }

    // Check & generate the JS output for the settings array
    $available_settings = array(
      'accessibility'    => 'boolean',
      'adaptiveHeight'   => 'boolean',
      'autoplay'         => 'boolean',
      'autoplaySpeed'    => 'int',
      'arrows'           => 'boolean',
      'appendArrows'     => 'mixed',
      'appendDots'       => 'mixed',
      'prevArrow'        => 'string',
      'nextArrow'        => 'string',
      'centerMode'       => 'boolean',
      'centerPadding'    => 'string',
      'cssEase'          => 'string',
      'customPaging'     => 'mixed',
      'dots'             => 'boolean',
      'dotsClass'        => 'string',
      'draggable'        => 'boolean',
      'fade'             => 'boolean',
      'focusOnSelect'    => 'boolean',
      'easing'           => 'string',
      'edgeFriction'     => 'int',
      'infinite'         => 'boolean',
      'initialSlide'     => 'int',
      'lazyLoad'         => 'string',
      'mobileFirst'      => 'boolean',
      'pauseOnFocus'     => 'boolean',
      'pauseOnHover'     => 'boolean',
      'pauseOnDotsHover' => 'boolean',
      'respondTo'        => 'string',
      'rows'             => 'int',
      'slide'            => 'string',
      'slidesPerRow'     => 'int',
      'slidesToShow'     => 'int',
      'slidesToScroll'   => 'int',
      'speed'            => 'int',
      'swipe'            => 'boolean',
      'swipeToSlide'     => 'boolean',
      'touchMove'        => 'boolean',
      'touchThreshold'   => 'int',
      'useCSS'           => 'boolean',
      'useTransform'     => 'boolean',
      'variableWidth'    => 'boolean',
      'vertical'         => 'boolean',
      'verticalSwiping'  => 'boolean',
      'rtl'              => 'boolean',
      'waitForAnimate'   => 'boolean',
      'zIndex'           => 'int',
    );

    // Loop through the available settings & add to JS settings output
    $js_settings = '';
    foreach( $available_settings as $name => $type ) {

      // Check if the setting has been defined
      if ( isset( $settings[ $name ] ) ) {
        if ( $js_settings ) $js_settings .= ',';

        switch( $type ) {
          case 'boolean':
            $js_settings .= $name . ': ' . ( boolval( $settings[ $name ] ) ? 'true' : 'false' );
            break;
          case 'int':
            $js_settings .= $name . ': ' . floatval( $settings[ $name ] );
            break;
          case 'mixed':
            $js_settings .= $name . ': ' . $settings[ $name ];
            break;
          case 'string':
            $js_settings .= $name . ": '" . $settings[ $name ] . "'";
            break;
        }
      }

    }

    return $js_settings;
  }
}
