<?php
/**
 * Core class
 *
 * @package     WebMan WordPress Theme Framework (Simple)
 * @subpackage  Core
 *
 * @since    2.0
 * @version  2.0
 */





if ( ! class_exists( 'WM_Theme_Framework' ) ) {
	final class WM_Theme_Framework {

		/**
		 * Contents:
		 *
		 *   0) Theme upgrade action
		 *  10) Branding
		 *  20) SEO
		 *  30) Post/page
		 *  40) CSS functions
		 * 100) Helpers
		 */





		/**
		 * 0) Theme upgrade action
		 */

			/**
			 * Do action on theme version change
			 *
			 * @since    1.0
			 * @version  2.0
			 */
			public static function theme_upgrade() {

				//Helper variables

					$current_theme_version = get_transient( WMTF_THEME_SHORTNAME . '_version' );


				//Processing

					if (
							empty( $current_theme_version )
							|| wp_get_theme()->get( 'Version' ) != $current_theme_version
						) {

						do_action( 'wmhook_theme_upgrade' );

						set_transient( WMTF_THEME_SHORTNAME . '_version', wp_get_theme()->get( 'Version' ) );

					}

			} // /theme_upgrade





		/**
		 * 10) Branding
		 */

			/**
			 * Get the logo
			 *
			 * Supports Jetpack Site Logo module.
			 *
			 * @since    1.0
			 * @version  2.0
			 */
			public static function get_the_logo() {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_get_the_logo_pre', false );

					if ( false !== $pre ) {
						echo $pre;
						return;
					}


				//Helper variables

					$output = '';

					$blog_info = apply_filters( 'wmhook_wmtf_get_the_logo_blog_info', array(
							'name'        => trim( get_bloginfo( 'name' ) ),
							'description' => trim( get_bloginfo( 'description' ) ),
						) );

					$args = apply_filters( 'wmhook_wmtf_get_the_logo_args', array(
							'logo_image' => ( function_exists( 'jetpack_get_site_logo' ) ) ? ( absint( jetpack_get_site_logo( 'id' ) ) ) : ( false ),
							'logo_type'  => 'text',
							'title_att'  => ( $blog_info['description'] ) ? ( $blog_info['name'] . ' | ' . $blog_info['description'] ) : ( $blog_info['name'] ),
							'url'        => home_url( '/' ),
						) );


				//Processing

					//Logo image

						if ( ! empty( $args['logo_image'] ) ) {

							$img_id = ( is_numeric( $args['logo_image'] ) ) ? ( absint( $args['logo_image'] ) ) : ( self::get_image_id_from_url( $args['logo_image'] ) );

							if ( $img_id ) {

								$logo_url = wp_get_attachment_image_src( $img_id, 'full' );

								$atts = (array) apply_filters( 'wmhook_wmtf_get_the_logo_image_atts', array(
										'alt'   => esc_attr( sprintf( _x( '%s logo', 'Site logo image "alt" HTML attribute text.', 'wmtf_domain' ), $blog_info['name'] ) ),
										'title' => esc_attr( $args['title_att'] ),
										'class' => '',
									), $img_id );

								$args['logo_image'] = wp_get_attachment_image( absint( $img_id ), 'full', false, $atts );

							}

							$args['logo_type'] = 'img';

						}

						$args['logo_image'] = apply_filters( 'wmhook_wmtf_get_the_logo_image', $args['logo_image'] );

					//Logo HTML

						$output .= '<div class="site-branding">';
							$output .= '<h1 class="' . esc_attr( apply_filters( 'wmhook_wmtf_get_the_logo_class', 'site-title logo type-' . $args['logo_type'], $args ) ) . '">';
							$output .= '<a href="' . esc_url( $args['url'] ) . '" title="' . esc_attr( $args['title_att'] ) . '">';

									if ( 'text' === $args['logo_type'] ) {
										$output .= '<span class="text-logo">' . $blog_info['name'] . '</span>';
									} else {
										$output .= $args['logo_image'];
									}

							$output .= '</a></h1>';

								if ( $blog_info['description'] ) {
									$output .= '<h2 class="site-description">' . $blog_info['description'] . '</h2>';
								}

						$output .= '</div>';


				//Output

					echo $output;

			} // /get_the_logo



				/**
				 * Display the logo
				 *
				 * @since    2.0
				 * @version  2.0
				 */
				public static function the_logo() {

					//Helper variables

						$output = self::get_the_logo();


					//Output

						if ( $output ) {
							echo $output;
						}

				} // /the_logo





		/**
		 * 20) SEO
		 */

			/**
			 * Schema.org markup on HTML tags
			 *
			 * @uses    schema.org
			 * @link    http://schema.org/docs/gs.html
			 * @link    http://leaves-and-love.net/how-to-improve-wordpress-seo-with-schema-org/
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param   string  $element
			 * @param   boolean $output_meta_tag  Wraps output in a <meta> tag.
			 *
			 * @return  string Schema.org HTML attributes
			 */
			public static function schema_org( $element = '', $output_meta_tag = false ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_schema_org_pre', false, $element, $output_meta_tag );

					if ( false !== $pre ) {
						return $pre;
					}


				//Requirements check

					if ( empty( $element ) ) {
						return;
					} else if ( function_exists( 'wma_schema_org' ) ) {
						return wma_schema_org( $element, $output_meta_tag );
					}


				//Helper variables

					$output = '';

					$base    = esc_attr( apply_filters( 'wmhook_wmtf_schema_org_base', 'http://schema.org/', $element, $output_meta_tag ) );
					$post_id = ( is_home() ) ? ( get_option( 'page_for_posts' ) ) : ( null );
					$type    = get_post_meta( $post_id, 'schemaorg_type', true );

					//Add custom post types that describe a single item to this array

						$itempage_array = (array) apply_filters( 'wmhook_wmtf_schema_org_itempage_array', array( 'jetpack-portfolio' ), $element, $output_meta_tag );


				//Processing

					switch ( $element ) {

						case 'author':
								$output = 'itemprop="author"';
							break;

						case 'datePublished':
								$output = 'itemprop="datePublished"';
							break;

						case 'entry':
								$output = 'itemscope ';

								if ( is_page() ) {
									$output .= 'itemtype="' . $base . 'WebPage"';

								} elseif ( is_singular( 'jetpack-portfolio' ) ) {
									$output .= 'itemprop="workExample" itemtype="' . $base . 'CreativeWork"';

								} elseif ( 'audio' === get_post_format() ) {
									$output .= 'itemtype="' . $base . 'AudioObject"';

								} elseif ( 'gallery' === get_post_format() ) {
									$output .= 'itemprop="ImageGallery" itemtype="' . $base . 'ImageGallery"';

								} elseif ( 'video' === get_post_format() ) {
									$output .= 'itemprop="video" itemtype="' . $base . 'VideoObject"';

								} else {
									$output .= 'itemprop="blogPost" itemtype="' . $base . 'BlogPosting"';

								}
							break;

						case 'entry_body':
								if ( ! is_single() ) {
									$output = 'itemprop="description"';

								} elseif ( is_page() ) {
									$output = 'itemprop="mainContentOfPage"';

								} else {
									$output = 'itemprop="articleBody"';

								}
							break;

						case 'image':
								$output = 'itemprop="image"';
							break;

						case 'ItemList':
								$output = 'itemscope itemtype="' . $base . 'ItemList"';
							break;

						case 'keywords':
								$output = 'itemprop="keywords"';
							break;

						case 'name':
								$output = 'itemprop="name"';
							break;

						case 'Person':
								$output = 'itemscope itemtype="' . $base . 'Person"';
							break;

						case 'SiteNavigationElement':
								$output = 'itemscope itemtype="' . $base . 'SiteNavigationElement"';
							break;

						case 'url':
								$output = 'itemprop="url"';
							break;

						case 'WPFooter':
								$output = 'itemscope itemtype="' . $base . 'WPFooter"';
							break;

						case 'WPSideBar':
								$output = 'itemscope itemtype="' . $base . 'WPSideBar"';
							break;

						case 'WPHeader':
								$output = 'itemscope itemtype="' . $base . 'WPHeader"';
							break;

						default:
								$output = $element;
							break;

					} // /switch

					$output = ' ' . $output;

					//Output in <meta> tag

						if ( $output_meta_tag ) {
							if ( false === strpos( $output, 'content=' ) ) {
								$output .= ' content="true"';
							}
							$output = '<meta ' . trim( $output ) . ' />';
						}

				//Output

					return $output;

			} // /schema_org





		/**
		 * 30) Post/page
		 */

			/**
			 * Add table of contents generated from <!--nextpage--> tag
			 *
			 * Will create a table of content in multipage post from
			 * the first H2 heading in each post part.
			 * Appends the output at the top and bottom of post content.
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  string $content
			 */
			public static function add_table_of_contents( $content = '' ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_add_table_of_contents_pre', false, $content );

					if ( false !== $pre ) {
						return $pre;
					}


				//Helper variables

					global $page, $numpages, $multipage, $post;

					//Requirements check

						if (
								! $multipage
								|| ! is_singular()
							) {
							return $content;
						}

					//translators: %s will be replaced with parted post title. Copy it, do not translate.
					$title_text = apply_filters( 'wmhook_wmtf_add_table_of_contents_title_text', sprintf( _x( '"%s" table of contents', 'Parted/paginated post table of content title. %s = post title.', 'wmtf_domain' ), the_title_attribute( 'echo=0' ) ) );
					$title      = apply_filters( 'wmhook_wmtf_add_table_of_contents_title', '<h2 class="screen-reader-text">' . $title_text . '</h2>' );

					$args = apply_filters( 'wmhook_wmtf_add_table_of_contents_args', array(
							'disable_first' => true, //First part to have a title of the post (part title won't be parsed)?
							'links'         => array(), //The output HTML links
							'post_content'  => ( isset( $post->post_content ) ) ? ( $post->post_content ) : ( '' ), //Get the whole post content
							'tag'           => 'h2', //HTML heading tag to parse as a post part title
						) );

					//Post part counter

						$i = 0;


				//Processing

					$args['post_content'] = explode( '<!--nextpage-->', (string) $args['post_content'] );

					//Get post parts titles

						foreach ( $args['post_content'] as $part ) {

							//Current post part number

								$i++;

							//Get the title for post part

								if ( $args['disable_first'] && 1 === $i ) {

									$part_title = the_title_attribute( 'echo=0' );

								} else {

									preg_match( '/<' . tag_escape( $args['tag'] ) . '(.*?)>(.*?)<\/' . tag_escape( $args['tag'] ) . '>/', $part, $matches );

									if ( ! isset( $matches[2] ) || ! $matches[2] ) {
										$part_title = sprintf( __( 'Page %d', 'wmtf_domain' ), $i );
									} else {
										$part_title = $matches[2];
									}

								}

							//Set post part class

								if ( $page === $i ) {
									$class = ' class="current"';
								} elseif ( $page > $i ) {
									$class = ' class="passed"';
								} else {
									$class = '';
								}

							//Post part item output

								$args['links'][$i] = (string) apply_filters( 'wmhook_wmtf_add_table_of_contents_part', '<li' . $class . '>' . _wp_link_page( $i ) . $part_title . '</a></li>', $i, $part_title, $class, $args );

						} // /foreach

					//Add table of contents into the post/page content

						$args['links'] = implode( '', $args['links'] );

						$links = apply_filters( 'wmhook_wmtf_add_table_of_contents_links', array(
								//Display table of contents before the post content only in first post part
									'before' => ( 1 === $page ) ? ( '<div class="post-table-of-contents top" title="' . esc_attr( strip_tags( $title_text ) ) . '">' . $title . '<ol>' . $args['links'] . '</ol></div>' ) : ( '' ),
								//Display table of cotnnets after the post cotnent on each post part
									'after'  => '<div class="post-table-of-contents bottom" title="' . esc_attr( strip_tags( $title_text ) ) . '">' . $title . '<ol>' . $args['links'] . '</ol></div>',
							), $args );

						$content = $links['before'] . $content . $links['after'];

				//Output

					return $content;

			} // /add_table_of_contents



			/**
			 * Get the post meta info
			 *
			 * hAtom microformats compatible. @link http://goo.gl/LHi4Dy
			 * Supports ZillaLikes plugin. @link http://www.themezilla.com/plugins/zillalikes/
			 * Supports Post Views Count plugin. @link https://wordpress.org/plugins/baw-post-views-count/
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  array $args
			 */
			public static function get_the_post_meta_info( $args = array() ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_get_the_post_meta_info_pre', false, $args );

					if ( false !== $pre ) {
						return $pre;
					}


				//Helper variables

					$output = '';

					$args = wp_parse_args( $args, apply_filters( 'wmhook_wmtf_get_the_post_meta_info_defaults', array(
							'class'       => 'entry-meta clearfix',
							'date_format' => null,
							'html'        => '<span class="{class}"{attributes}>{content}</span>',
							'html_custom' => array(
									'date' => '<time datetime="{datetime}" class="{class}"{attributes}>{content}</time>',
								),
							'meta'        => array(), //Example: array( 'date', 'author', 'category', 'comments', 'permalink' )
							'post_id'     => null,
						) ) );
					$args = apply_filters( 'wmhook_wmtf_get_the_post_meta_info_args', $args );

					$args['meta'] = array_filter( (array) $args['meta'] );

					if ( $args['post_id'] ) {
						$args['post_id'] = absint( $args['post_id'] );
					}


				//Requirements check

					if ( empty( $args['meta'] ) ) {
						return;
					}


				//Processing

					foreach ( $args['meta'] as $meta ) {

							$helper = '';

							$replacements  = (array) apply_filters( 'wmhook_wmtf_get_the_post_meta_info_replacements', array(), $meta, $args );
							$output_single = apply_filters( 'wmhook_wmtf_get_the_post_meta_info', '', $meta, $args );
							$output       .= $output_single;

						//Predefined metas

							switch ( $meta ) {

								case 'author':

									if ( apply_filters( 'wmhook_wmtf_get_the_post_meta_info_enable_' . $meta, true, $args ) ) {
										$helper = ( function_exists( 'wmtf_schema_org' ) ) ? ( wmtf_schema_org( 'author' ) ) : ( '' );

										$replacements = array(
												'{attributes}' => ( function_exists( 'wmtf_schema_org' ) ) ? ( wmtf_schema_org( 'Person' ) ) : ( '' ),
												'{class}'      => esc_attr( 'author vcard entry-meta-element' ),
												'{content}'    => '<a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '" class="url fn n" rel="author"' . $helper . '>' . get_the_author() . '</a>',
											);
									}

								break;
								case 'category':

									if (
											apply_filters( 'wmhook_wmtf_get_the_post_meta_info_enable_' . $meta, true, $args )
											&& self::is_categorized_blog()
											&& ( $helper = get_the_category_list( ', ', '', $args['post_id'] ) )
										) {
										$replacements = array(
												'{attributes}' => '',
												'{class}'      => esc_attr( 'cat-links entry-meta-element' ),
												'{content}'    => $helper,
											);
									}

								break;
								case 'comments':

									if (
											apply_filters( 'wmhook_wmtf_get_the_post_meta_info_enable_' . $meta, true, $args )
											&& ! post_password_required()
											&& (
												comments_open( $args['post_id'] )
												|| get_comments_number( $args['post_id'] )
											)
										) {
										$helper       = get_comments_number( $args['post_id'] );
										$element_id   = ( $helper ) ? ( '#comments' ) : ( '#respond' );
										$replacements = array(
												'{attributes}' => '',
												'{class}'      => esc_attr( 'comments-link entry-meta-element' ),
												'{content}'    => '<a href="' . esc_url( get_permalink( $args['post_id'] ) ) . $element_id . '" title="' . esc_attr( sprintf( _x( 'Comments: %s', 'Number of comments in post meta.', 'wmtf_domain' ), $helper ) ) . '">' . sprintf( _x( '<span class="comments-title">Comments: </span>%s', 'Number of comments in post meta (keep the HTML tags).', 'wmtf_domain' ), '<span class="comments-count">' . $helper . '</span>' ) . '</a>',
											);
									}

								break;
								case 'date':

									if ( apply_filters( 'wmhook_wmtf_get_the_post_meta_info_enable_' . $meta, true, $args ) ) {
										$helper = ( function_exists( 'wmtf_schema_org' ) ) ? ( wmtf_schema_org( 'datePublished' ) ) : ( '' );

										$replacements = array(
												'{attributes}' => ' title="' . esc_attr( get_the_date() ) . ' | ' . esc_attr( get_the_time( '', $args['post_id'] ) ) . '"' . $helper,
												'{class}'      => esc_attr( 'entry-date entry-meta-element published' ),
												'{content}'    => esc_html( get_the_date( $args['date_format'] ) ),
												'{datetime}'   => esc_attr( get_the_date( 'c' ) ),
											);
									}

								break;
								case 'edit':

									if (
											apply_filters( 'wmhook_wmtf_get_the_post_meta_info_enable_' . $meta, true, $args )
											&& ( $helper = get_edit_post_link( $args['post_id'] ) )
										) {
										$the_title_attribute_args = array( 'echo' => false );
										if ( $args['post_id'] ) {
											$the_title_attribute_args['post'] = $args['post_id'];
										}

										$replacements = array(
												'{attributes}' => '',
												'{class}'      => esc_attr( 'entry-edit entry-meta-element' ),
												'{content}'    => '<a href="' . esc_url( $helper ) . '" title="' . esc_attr( sprintf( __( 'Edit the "%s"', 'wmtf_domain' ), the_title_attribute( $the_title_attribute_args ) ) ) . '"><span>' . _x( 'Edit', 'Edit post link.', 'wmtf_domain' ) . '</span></a>',
											);
									}

								break;
								case 'likes':

									if (
											apply_filters( 'wmhook_wmtf_get_the_post_meta_info_enable_' . $meta, true, $args )
											&& function_exists( 'zilla_likes' )
										) {
										global $zilla_likes;
										$helper = $zilla_likes->do_likes();

										$replacements = array(
												'{attributes}' => '',
												'{class}'      => esc_attr( 'entry-likes entry-meta-element' ),
												'{content}'    => $helper,
											);
									}

								break;
								case 'permalink':

									if ( apply_filters( 'wmhook_wmtf_get_the_post_meta_info_enable_' . $meta, true, $args ) ) {
										$the_title_attribute_args = array( 'echo' => false );
										if ( $args['post_id'] ) {
											$the_title_attribute_args['post'] = $args['post_id'];
										}

										$replacements = array(
												'{attributes}' => ( function_exists( 'wmtf_schema_org' ) ) ? ( wmtf_schema_org( 'url' ) ) : ( '' ),
												'{class}'      => esc_attr( 'entry-permalink entry-meta-element' ),
												'{content}'    => '<a href="' . esc_url( get_permalink( $args['post_id'] ) ) . '" title="' . esc_attr( sprintf( __( 'Permalink to "%s"', 'wmtf_domain' ), the_title_attribute( $the_title_attribute_args ) ) ) . '" rel="bookmark"><span>' . get_the_title( $args['post_id'] ) . '</span></a>',
											);
									}

								break;
								case 'tags':

									if (
											apply_filters( 'wmhook_wmtf_get_the_post_meta_info_enable_' . $meta, true, $args )
											&& ( $helper = get_the_tag_list( '', ' ', '', $args['post_id'] ) )
										) {
										$replacements = array(
												'{attributes}' => ( function_exists( 'wmtf_schema_org' ) ) ? ( wmtf_schema_org( 'keywords' ) ) : ( '' ),
												'{class}'      => esc_attr( 'tags-links entry-meta-element' ),
												'{content}'    => $helper,
											);
									}

								break;
								case 'views':

									if (
											apply_filters( 'wmhook_wmtf_get_the_post_meta_info_enable_' . $meta, true, $args )
											&& function_exists( 'bawpvc_views_sc' )
											&& ( $helper = bawpvc_views_sc( array() ) )
										) {
										$replacements = array(
												'{attributes}' => ' title="' . __( 'Views count', 'wmtf_domain' ) . '"',
												'{class}'      => esc_attr( 'entry-views entry-meta-element' ),
												'{content}'    => $helper,
											);
									}

								break;

								default:
								break;

							} // /switch

							//Single meta output

								$replacements = (array) apply_filters( 'wmhook_wmtf_get_the_post_meta_info_replacements_' . $meta, $replacements, $args );

								if (
										empty( $output_single )
										&& ! empty( $replacements )
									) {

									if ( isset( $args['html_custom'][ $meta ] ) ) {
										$output .= strtr( $args['html_custom'][ $meta ], (array) $replacements );
									} else {
										$output .= strtr( $args['html'], (array) $replacements );
									}

								}

					} // /foreach

					if ( $output ) {
						$output = '<div class="' . esc_attr( $args['class'] ) . '">' . $output . '</div>';
					}


				//Output

					return $output;

			} // /get_the_post_meta_info



				/**
				 * Display the post meta info
				 *
				 * @since    2.0
				 * @version  2.0
				 *
				 * @param  array $args
				 */
				public static function the_post_meta_info( $args = array() ) {

					//Helper variables

						$output = self::get_the_post_meta_info( $args );


					//Output

						if ( $output ) {
							echo $output;
						}

				} // /the_post_meta_info



			/**
			 * Get the paginated heading suffix
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  string $tag           Wrapper tag
			 * @param  string $singular_only Display only on singular posts of specific type
			 */
			public static function get_the_paginated_suffix( $tag = '', $singular_only = false ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_get_the_paginated_suffix_pre', false, $tag, $singular_only );

					if ( false !== $pre ) {
						return $pre;
					}


				//Requirements check

					if (
							$singular_only
							&& ! is_singular( $singular_only )
						) {
						return;
					}


				//Helper variables

					global $page, $paged;

					$output = '';

					if ( ! isset( $paged ) ) {
						$paged = 0;
					}
					if ( ! isset( $page ) ) {
						$page = 0;
					}

					$paged = max( $page, $paged );

					$tag = trim( $tag );
					if ( $tag ) {
						$tag = array( '<' . tag_escape( $tag ) . '>', '</' . tag_escape( $tag ) . '>' );
					} else {
						$tag = array( '', '' );
					}


				//Processing

					if ( 1 < $paged ) {
						$output = ' ' . $tag[0] . sprintf( _x( '(page %s)', 'Paginated content title suffix.', 'wmtf_domain' ), $paged ) . $tag[1];
					}


				//Output

					return $output;

			} // /get_the_paginated_suffix



				/**
				 * Display the paginated heading suffix
				 *
				 * @since    2.0
				 * @version  2.0
				 *
				 * @param  string $tag           Wrapper tag
				 * @param  string $singular_only Display only on singular posts of specific type
				 */
				public static function the_paginated_suffix( $tag = '', $singular_only = false ) {

					//Helper variables

						$output = self::get_the_paginated_suffix( $tag, $singular_only );


					//Output

						if ( $output ) {
							echo $output;
						}

				} // /the_paginated_suffix



			/**
			 * Checks for <!--more--> tag in post content
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  mixed $post
			 */
			public static function has_more_tag( $post = null ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_has_more_tag_pre', false, $post );

					if ( false !== $pre ) {
						return $pre;
					}


				//Helper variables

					if ( empty( $post ) ) {
						global $post;
					} elseif ( is_numeric( $post ) ) {
						$post = get_post( absint( $post ) );
					}


				//Requirements check

					if (
							! is_object( $post )
							|| ! isset( $post->post_content )
						) {
						return;
					}


				//Output

					return strpos( $post->post_content, '<!--more-->' );

			} // /has_more_tag





		/**
		 * 40) CSS functions
		 */

			/**
			 * CSS escaping
			 *
			 * Use this for custom CSS output only!
			 * Uses `esc_attr()` while keeping quote marks.
			 *
			 * @uses  esc_attr()
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  string $css Code to escape
			 */
			public static function esc_css( $css ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_esc_css_pre', false, $css );

					if ( false !== $pre ) {
						return $pre;
					}


				//Output

					return str_replace( array( '&gt;', '&quot;', '&#039;' ), array( '>', '"', '\'' ), esc_attr( (string) $css ) );

			} // /esc_css



			/**
			 * Outputs path to the specific file
			 *
			 * This function looks for the file in the child theme first.
			 * If the file is not located in child theme, outputs the path from parent theme.
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  string $file_relative_path File to look for (insert also the theme structure relative path)
			 *
			 * @return  string Actual path to the file
			 */
			public static function get_stylesheet_directory( $file_relative_path ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_get_stylesheet_directory_pre', false, $file_relative_path );

					if ( false !== $pre ) {
						return $pre;
					}


				//Helper variables

					$output = '';

					$file_relative_path = trim( $file_relative_path );


				//Requirements chek

					if ( ! $file_relative_path ) {
						return;
					}


				//Processing

					if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file_relative_path ) ) {
						$output = trailingslashit( get_stylesheet_directory() ) . $file_relative_path;
					} else {
						$output = trailingslashit( get_template_directory() ) . $file_relative_path;
					}


				//Output

					return $output;

			} // /get_stylesheet_directory



			/**
			 * Outputs URL to the specific file
			 *
			 * This function looks for the file in the child theme first.
			 * If the file is not located in child theme, output the URL from parent theme.
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  string $file_relative_path File to look for (insert also the theme structure relative path)
			 *
			 * @return  string Actual URL to the file
			 */
			public static function get_stylesheet_directory_uri( $file_relative_path ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_get_stylesheet_directory_uri_pre', false, $file_relative_path );

					if ( false !== $pre ) {
						return $pre;
					}


				//Helper variables

					$output = '';

					$file_relative_path = trim( $file_relative_path );


				//Requirements chek

					if ( ! $file_relative_path ) {
						return;
					}


				//Processing

					if ( file_exists( trailingslashit( get_stylesheet_directory() ) . $file_relative_path ) ) {
						$output = trailingslashit( get_stylesheet_directory_uri() ) . $file_relative_path;
					} else {
						$output = trailingslashit( get_template_directory_uri() ) . $file_relative_path;
					}


				//Output

					return $output;

			} // /get_stylesheet_directory_uri



			/**
			 * CSS minifier
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  string $css Code to minimize
			 */
			public static function minify_css( $css ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_minify_css_pre', false, $css );

					if ( false !== $pre ) {
						return $pre;
					}


				//Requirements check

					if ( ! is_string( $css ) ) {
						return $css;
					}


				//Processing

					//Remove CSS comments

						$css = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css );

					//Remove tabs, spaces, line breaks, etc.

						$css = str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $css );
						$css = str_replace( array( '  ', '   ', '    ', '     ' ), ' ', $css );
						$css = str_replace( array( ' { ', ': ', '; }' ), array( '{', ':', '}' ), $css );


				//Output

					return $css;

			} // /minify_css



			/**
			 * Hex color to RGBA
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @link  http://php.net/manual/en/function.hexdec.php
			 *
			 * @param  string $hex
			 * @param  absint $alpha [0-100]
			 *
			 * @return  string Color in rgb() or rgba() format to use in CSS.
			 */
			public static function color_hex_to_rgba( $hex, $alpha = 100 ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_color_hex_to_rgba_pre', false, $hex, $alpha );

					if ( false !== $pre ) {
						return $pre;
					}


				//Helper variables

					$alpha = absint( $alpha );

					$output = ( 100 === $alpha ) ? ( 'rgb(' ) : ( 'rgba(' );

					$rgb = array();

					$hex = preg_replace( '/[^0-9A-Fa-f]/', '', $hex );
					$hex = substr( $hex, 0, 6 );


				//Processing

					//Converting hex color into rgb

						$color = (int) hexdec( $hex );

						$rgb['r'] = (int) 0xFF & ( $color >> 0x10 );
						$rgb['g'] = (int) 0xFF & ( $color >> 0x8 );
						$rgb['b'] = (int) 0xFF & $color;

						$output .= implode( ',', $rgb );

					//Using alpha (rgba)?

						if ( 100 > $alpha ) {
							$output .= ',' . ( $alpha / 100 );
						}


				//Output

					return $output;

			} // /color_hex_to_rgba

			/**
			 * Outputs custom CSS styles set via Customizer
			 *
			 * This function allows you to hook your custom CSS styles string
			 * onto 'wmhook_custom_styles' filter hook.
			 * Then just use a '[[skin-option-id]]' tags in your custom CSS
			 * styles string where the specific option value should be used.
			 *
			 * Caching $replacement into 'WMTF_THEME_SHORTNAME_customizer_values' transient.
			 * Caching $output into 'WMTF_THEME_SHORTNAME_custom_css' transient.
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  bool $set_cache  Determines whether the results should be cached or not.
			 * @param  bool $return     Whether to return a value or just run the process.
			 */
			public static function custom_styles( $set_cache = false, $return = true ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_custom_styles_pre', false, $set_cache, $return );

					if ( false !== $pre ) {
						return $pre;
					}


				//Helper variables

					global $wp_customize;

					if ( ! isset( $wp_customize ) || ! is_object( $wp_customize ) ) {
						$wp_customize = null;
					}

					$output        = (string) apply_filters( 'wmhook_custom_styles', '' );
					$theme_options = (array) apply_filters( 'wmhook_theme_options', array() );
					$alphas        = (array) apply_filters( 'wmhook_wmtf_custom_styles_alphas', array( 0 ), $option );

					$replacements  = array_unique( array_filter( (array) get_transient( WMTF_THEME_SHORTNAME . '_customizer_values' ) ) ); //There have to be values (defaults) set!

					/**
					 * Force caching during the first theme display when no cache set (default
					 * values will be used).
					 * Cache is being set only after saving Customizer.
					 */
					if ( empty( $replacements ) ) {
						$set_cache = true;
					}


				//Processing

					/**
					 * Setting up replacements array when no cache exists.
					 * Also, creates a new cache for replacements values.
					 * The cache is being created only when saving the Customizer settings.
					 */

						if (
								! empty( $theme_options )
								&& (
									( $wp_customize && $wp_customize->is_preview() )
									|| empty( $replacements )
								)
							) {

							foreach ( $theme_options as $option ) {

								//Reset variables

									$option_id = $value = '';

								//Set option ID

									if ( isset( $option['id'] ) ) {
										$option_id = $option['id'];
									}

								//If no option ID set, jump to next option

									if ( empty( $option_id ) ) {
										continue;
									}

								//If we have an ID, get the default value if set

									if ( isset( $option['default'] ) ) {
										$value = $option['default'];
									}

								//Get the option value saved in database and apply it when exists

									if ( $mod = get_theme_mod( $option_id ) ) {
										$value = $mod;
									}

								//Make sure the color value contains '#'

									if ( 'color' === $option['type'] ) {
										$value = '#' . trim( $value, '#' );
									}

								//Make sure the image URL is used in CSS format

									if ( 'image' === $option['type'] ) {
										if ( is_array( $value ) && isset( $value['id'] ) ) {
											$value = absint( $value['id'] );
										}
										if ( is_numeric( $value ) ) {
											$value = wp_get_attachment_image_src( $value, 'full' );
											$value = $value[0];
										}
										if ( ! empty( $value ) ) {
											$value = "url('" . esc_url( $value ) . "')";
										} else {
											$value = 'none';
										}
									}

								//Value filtering

									$value = apply_filters( 'wmhook_wmtf_custom_styles_value', $value, $option );

								//Convert array to string as otherwise the strtr() function throws error

									if ( is_array( $value ) ) {
										$value = (string) implode( ',', (array) $value );
									}

								//Finally, modify the output string

									$replacements['[[' . $option_id . ']]'] = $value;

									//Add also rgba() color interpratation

										if ( 'color' === $option['type'] ) {
											foreach ( $alphas as $alpha ) {
												$replacements['[[' . $option_id . '|alpha=' . absint( $alpha ) . ']]'] = WM_Theme_Framework::color_hex_to_rgba( $value, absint( $alpha ) );
											} // /foreach
										}

							} // /foreach

							//Add WordPress Custom Background and Header support

								//Background color

									if ( $value = get_background_color() ) {
										$replacements['[[background_color]]'] = '#' . trim( $value, '#' );

										foreach ( $alphas as $alpha ) {
											$replacements['[[background_color|alpha=' . absint( $alpha ) . ']]'] = WM_Theme_Framework::color_hex_to_rgba( $value, absint( $alpha ) );
										} // /foreach
									}

								//Background image

									if ( $value = esc_url( get_background_image() ) ) {
										$replacements['[[background_image]]'] = "url('" . $value . "')";
									} else {
										$replacements['[[background_image]]'] = 'none';
									}

								//Header text color

									if ( $value = get_header_textcolor() ) {
										$replacements['[[header_textcolor]]'] = '#' . trim( $value, '#' );

										foreach ( $alphas as $alpha ) {
											$replacements['[[header_textcolor|alpha=' . absint( $alpha ) . ']]'] = WM_Theme_Framework::color_hex_to_rgba( $value, absint( $alpha ) );
										} // /foreach
									}

								//Header image

									if ( $value = esc_url( get_header_image() ) ) {
										$replacements['[[header_image]]'] = "url('" . $value . "')";
									} else {
										$replacements['[[header_image]]'] = 'none';
									}

							$replacements = apply_filters( 'wmhook_wmtf_custom_styles_replacements', $replacements, $theme_options, $output );

							if (
									$set_cache
									&& ! empty( $replacements )
								) {
								set_transient( WMTF_THEME_SHORTNAME . '_customizer_values', $replacements );
							}

						}

					//Prepare output and cache

						$output_cached = (string) get_transient( WMTF_THEME_SHORTNAME . '_custom_css' );

						//Debugging set (via "debug" URL parameter)

							if ( isset( $_GET['debug'] ) ) {
								$output_cached = (string) get_transient( WMTF_THEME_SHORTNAME . '_custom_css_debug' );
							}

						if (
								empty( $output_cached )
								|| ( $wp_customize && $wp_customize->is_preview() )
							) {

							//Replace tags in custom CSS strings with actual values

								$output = strtr( $output, $replacements );

							if ( $set_cache ) {
								set_transient( WMTF_THEME_SHORTNAME . '_custom_css_debug', apply_filters( 'wmhook_wmtf_custom_styles_output_cache_debug', $output ) );
								set_transient( WMTF_THEME_SHORTNAME . '_custom_css', apply_filters( 'wmhook_wmtf_custom_styles_output_cache', $output ) );
							}

						} else {

							$output = $output_cached;

						}


				//Output

					if ( $output && $return ) {
						return trim( (string) $output );
					}

			} // /custom_styles



				/**
				 * Flush out the transients used in `custom_styles`
				 *
				 * @since    1.0
				 * @version  2.0
				 */
				public static function custom_styles_transient_flusher() {

					//Processing

						delete_transient( WMTF_THEME_SHORTNAME . '_customizer_values' );
						delete_transient( WMTF_THEME_SHORTNAME . '_custom_css_debug' );
						delete_transient( WMTF_THEME_SHORTNAME . '_custom_css' );

				} // /custom_styles_transient_flusher



				/**
				 * Force cache only for the above function
				 *
				 * Useful to pass into the action hooks.
				 *
				 * @since    1.0
				 * @version  2.0
				 */
				public static function custom_styles_cache() {

					//Processing

						//Set cache, do not return

							self::custom_styles( true, false );

				} // /custom_styles_cache





		/**
		 * 100) Helpers
		 */

			/**
			 * Get Google Fonts link
			 *
			 * Returns a string such as:
			 * //fonts.googleapis.com/css?family=Alegreya+Sans:300,400|Exo+2:400,700|Allan&subset=latin,latin-ext
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  array $fonts Fallback fonts.
			 */
			public static function google_fonts_url( $fonts = array() ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_google_fonts_url_pre', false, $fonts );

					if ( false !== $pre ) {
						return $pre;
					}


				//Helper variables

					$output = '';
					$family = array();
					$subset = get_theme_mod( 'font-subset' );

					$fonts_setup = array_unique( array_filter( (array) apply_filters( 'wmhook_wmtf_google_fonts_url_fonts_setup', array() ) ) );

					if ( empty( $fonts_setup ) && ! empty( $fonts ) ) {
						$fonts_setup = (array) $fonts;
					}


				//Requirements check

					if ( empty( $fonts_setup ) ) {
						return;
					}


				//Processing

					foreach ( $fonts_setup as $section ) {

						$font = trim( $section );

						if ( $font ) {
							$family[] = str_replace( ' ', '+', $font );
						}

					} // /foreach

					if ( ! empty( $family ) ) {
						$output = esc_url( add_query_arg( array(
								'family' => implode( '|', (array) array_unique( $family ) ),
								'subset' => implode( ',', (array) $subset ), //Subset can be array if multiselect Customizer input field used
							), '//fonts.googleapis.com/css' ) );
					}


				//Output

					return preg_replace( '|\[(.+?)\]|s', '', $content );

			} // /google_fonts_url



			/**
			 * Remove shortcodes from string
			 *
			 * This function keeps the text between shortcodes,
			 * unlike WordPress native strip_shortcodes() function.
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  string $content
			 */
			public static function remove_shortcodes( $content ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_remove_shortcodes_pre', false, $content );

					if ( false !== $pre ) {
						return $pre;
					}


				//Output

					return preg_replace( '|\[(.+?)\]|s', '', $content );

			} // /remove_shortcodes



			/**
			 * HTML in widget titles
			 *
			 * Just replace the "<" and ">" in HTML tag with "[" and "]".
			 * Examples:
			 * "[em][/em]" will output "<em></em>"
			 * "[br /]" will output "<br />"
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  string $title
			 */
			public static function html_widget_title( $title ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_html_widget_title_pre', false, $title );

					if ( false !== $pre ) {
						return $pre;
					}


				//Helper variables

					$replacements = array(
							'[' => '<',
							']' => '>',
						);


				//Output

					return wp_kses_post( strtr( $title, $replacements ) );

			} // /html_widget_title



			/**
			 * Accessibility skip links
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @param  string $type
			 */
			public static function accessibility_skip_link( $type ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_accessibility_skip_link_pre', false, $type );

					if ( false !== $pre ) {
						return $pre;
					}


				//Helper variables

					$links = apply_filters( 'wmhook_wmtf_accessibility_skip_links', array(
						'to_content'    => '<a class="skip-link screen-reader-text" href="#content">' . __( 'Skip to content', 'wmtf_domain' ) . '</a>',
						'to_navigation' => '<a class="skip-link screen-reader-text" href="#site-navigation">' . __( 'Skip to navigation', 'wmtf_domain' ) . '</a>',
					) );


				//Output

					if ( isset( $links[ $type ] ) ) {
						return $links[ $type ];
					}

			} // /accessibility_skip_link



			/**
			 * Get image ID from its URL
			 *
			 * @since    1.0
			 * @version  2.0
			 *
			 * @link  http://pippinsplugins.com/retrieve-attachment-id-from-image-url/
			 * @link  http://make.wordpress.org/core/2012/12/12/php-warning-missing-argument-2-for-wpdb-prepare/
			 *
			 * @param  string $url
			 */
			public static function get_image_id_from_url( $url ) {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_get_image_id_from_url_pre', false, $url );

					if ( false !== $pre ) {
						return $pre;
					}


				//Helper variables

					global $wpdb;

					$output = null;

					$cache = array_filter( (array) get_transient( 'wmtf_image_ids' ) );


				//Return cached result if found and if relevant

					if (
							! empty( $cache )
							&& isset( $cache[ $url ] )
							&& wp_get_attachment_url( absint( $cache[ $url ] ) )
							&& $url == wp_get_attachment_url( absint( $cache[ $url ] ) )
						) {

						return absint( $cache[ $url ] );

					}


				//Processing

					if (
							is_object( $wpdb )
							&& isset( $wpdb->prefix )
						) {

						$prefix = $wpdb->prefix;

						$attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM " . $prefix . "posts" . " WHERE guid = %s", esc_url( $url ) ) );

						$output = ( isset( $attachment[0] ) ) ? ( $attachment[0] ) : ( null );

					}

					//Cache the new record

						$cache[ $url ] = $output;

						set_transient( 'wmtf_image_ids', array_filter( (array) $cache ) );


				//Output

					return absint( $output );

			} // /get_image_id_from_url



				/**
				 * Flush out the transients used in `get_image_id_from_url`
				 *
				 * @since    1.0
				 * @version  2.0
				 */
				public static function image_ids_transient_flusher() {

					//Processing

						delete_transient( 'wmtf_image_ids' );

				} // /image_ids_transient_flusher



			/**
			 * Returns true if a blog has more than 1 category
			 *
			 * @since    1.0
			 * @version  2.0
			 */
			public static function is_categorized_blog() {

				//Pre

					$pre = apply_filters( 'wmhook_wmtf_is_categorized_blog_pre', false );

					if ( false !== $pre ) {
						return $pre;
					}


				//Processing

					if ( false === ( $all_cats = get_transient( 'wmtf_all_categories' ) ) ) {

						//Create an array of all the categories that are attached to posts

							$all_cats = get_categories( array(
									'fields'     => 'ids',
									'hide_empty' => 1,
									'number'     => 2, //we only need to know if there is more than one category
								) );

						//Count the number of categories that are attached to the posts

							$all_cats = count( $all_cats );

						set_transient( 'wmtf_all_categories', $all_cats );

					}


				//Output

					if ( $all_cats > 1 ) {

						//This blog has more than 1 category

							return true;

					} else {

						//This blog has only 1 category

							return false;

					}

			} // /is_categorized_blog



				/**
				 * Flush out the transients used in `is_categorized_blog`
				 *
				 * @since    1.0
				 * @version  2.0
				 */
				public static function all_categories_transient_flusher() {

					//Requirements check

						if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
							return;
						}


					//Processing

						//Like, beat it. Dig?

							delete_transient( 'wmtf_all_categories' );

				} // /all_categories_transient_flusher

	}
} // /WM_Theme_Framework
