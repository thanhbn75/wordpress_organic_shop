<?php
/**
 * Merchant Field: Flexible Content
 *
 * A repeatable, sortable container that can hold multiple layout types,
 * each with its own set of sub-fields. Supports accordion, sorting, and duplication.
 *
 * @package Merchant
 * @since   2.2.5
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Merchant_Field_Flexible_Content
 *
 * Renders a repeatable, sortable container of layout-based sub-fields.
 *
 * @since 2.2.5
 */
class Merchant_Field_Flexible_Content extends Merchant_Abstract_Field {

	/**
	 * Render the flexible content field.
	 *
	 * Outputs a sortable, accordion-enabled container of layout rows,
	 * each containing its own set of sub-fields. Also renders a hidden
	 * template copy of each layout type for JavaScript cloning.
	 *
	 * @since 2.2.5
	 *
	 * @return void
	 */
	public function render() {
		$this->get_template_part();
	}


	/**
	 * Type-specific sanitization for the flexible content field.
	 *
	 * Iterates each layout row, sanitizing every sub-field value
	 * using the field registry to locate the appropriate sanitizer.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value.
	 *
	 * @return mixed The sanitized value.
	 */
	protected function sanitize_value( $value ) {
		$value = ! is_array( $value ) || empty( $value ) ? array() : $value;

		return array_map( function ( $sub_fields ) {
			$layout_key = $sub_fields['layout'] ?? '';

			foreach ( $sub_fields as $sub_field => $sub_value ) {
				if ( 'layout' === $sub_field ) {
					$sub_fields[ $sub_field ] = sanitize_text_field( $sub_value );
				} else {
					$sub_fields[ $sub_field ] = $this->sanitize_sub_field( $layout_key, $sub_field, $sub_value );
				}
			}

			return $sub_fields;
		}, $value );
	}

	/**
	 * Sanitize a single sub-field value within a layout row.
	 *
	 * Locates the field definition from the layout configuration and
	 * delegates sanitization to the appropriate field instance via the
	 * registry. Falls back to plain-text sanitization for unknown
	 * fields or types.
	 *
	 * @since 2.2.5
	 *
	 * @param string $layout_key   The layout type key (e.g., 'default').
	 * @param string $sub_field_id The sub-field ID within the layout.
	 * @param mixed  $sub_value    The raw sub-field value.
	 *
	 * @return mixed The sanitized sub-field value.
	 */
	private function sanitize_sub_field( string $layout_key, string $sub_field_id, $sub_value ) {
		$layouts      = $this->field['layouts'] ?? array();
		$layout_field = null;

		foreach ( $layouts[ $layout_key ]['fields'] ?? array() as $field ) {
			if ( $field['id'] === $sub_field_id ) {
				$layout_field = $field;
				break;
			}
		}

		if ( ! $layout_field ) {
			// Unknown layout or sub-field — sanitize as plain text to prevent XSS.
			return is_array( $sub_value )
				? array_map( 'sanitize_text_field', $sub_value )
				: sanitize_text_field( $sub_value );
		}

		$registry = Merchant_Field_Registry::instance();
		$type     = $layout_field['type'] ?? '';

		if ( $registry->has( $type ) ) {
			$field_instance = $registry->create( $type, $layout_field, $sub_value );
			if ( $field_instance !== null ) {
				return $field_instance->sanitize( $sub_value );
			}
		}

		return sanitize_text_field( $sub_value );
	}

	/**
	 * Preprocess the flexible content value before sanitization.
	 *
	 * Ensures each layout row has a unique `flexible_id` and that
	 * the value is always an array. Sanitization of individual
	 * sub-field values is handled by sanitize_value() via the registry.
	 *
	 * @since 2.2.5
	 *
	 * @param mixed $value The raw submitted value (already unslashed).
	 *
	 * @return mixed The preprocessed value.
	 */
	public function preprocess( $value ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		foreach ( $value as &$item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			// Ensure each item has a flexible_id.
			if ( empty( $item['flexible_id'] ) ) {
				$item['flexible_id'] = wp_generate_uuid4();
			}
		}
		unset( $item );

		return $value;
	}

	/**
	 * Walk field definitions and resolve data for a specific field type.
	 *
	 * Recursively traverses layout field definitions (including nested
	 * fields_group entries) and delegates resolution of matching fields
	 * to the provided callable.
	 *
	 * @since 2.3
	 *
	 * @param array<string, mixed> $fields      Layout field definitions.
	 * @param array<string, mixed> $values      Saved values for the layout row.
	 * @param string               $target_type The field type to resolve (e.g., 'select_ajax').
	 * @param callable             $resolver    Callback receiving ($field_def, $raw_value) and returning
	 *                                          an array of resolved options or null to skip.
	 *
	 * @return array<string, mixed>
	 */
	private function walk_fields_of_type( array $fields, array $values, string $target_type, callable $resolver ): array {
		$result = array();

		foreach ( $fields as $field_def ) {
			$field_id   = $field_def['id'] ?? '';
			$field_type = $field_def['type'] ?? '';

			// Recurse into fields_group.
			if ( 'fields_group' === $field_type && ! empty( $field_def['fields'] ) ) {
				$group_values = $values[ $field_id ] ?? array();
				$group_result = $this->walk_fields_of_type(
					$field_def['fields'],
					is_array( $group_values ) ? $group_values : array(),
					$target_type,
					$resolver
				);

				if ( ! empty( $group_result ) ) {
					$result = array_merge( $result, $group_result );
				}

				continue;
			}

			if ( $target_type !== $field_type ) {
				continue;
			}

			$resolved = $resolver( $field_def, $values[ $field_id ] ?? null );

			if ( null !== $resolved ) {
				$result[ $field_id ] = $resolved;
			}
		}

		return $result;
	}

	/**
	 * Resolve selected option labels for select_ajax fields.
	 *
	 * Used when building deferred rendering data. Traverses the layout's field
	 * definitions and resolves {id, text} pairs for every select_ajax field
	 * that has a non-empty saved value.
	 *
	 * @since 2.3
	 *
	 * @param array<string, mixed> $fields Layout field definitions.
	 * @param array<string, mixed> $values Saved values for the layout row.
	 *
	 * @return array<string, array<int, array{id: string, text: string}>>
	 */
	public function resolve_select_options( array $fields, array $values ): array {
		return $this->walk_fields_of_type( $fields, $values, 'select_ajax', array( $this, 'resolve_single_select' ) );
	}

	/**
	 * Resolve a single select_ajax field's selected values to {id, text} pairs.
	 *
	 * @since 2.3
	 *
	 * @param array<string, mixed> $field_def The field definition.
	 * @param mixed                $raw_value The saved value.
	 *
	 * @return array<int, array{id: string, text: string}>|null Resolved options or null to skip.
	 */
	private function resolve_single_select( array $field_def, $raw_value ): ?array {
		// Coerce single value to array for uniform processing.
		if ( ! is_array( $raw_value ) ) {
			$raw_value = ( '' !== $raw_value && null !== $raw_value ) ? array( $raw_value ) : array();
		}

		if ( empty( $raw_value ) ) {
			return null;
		}

		$source  = $field_def['source'] ?? 'post';
		$options = array();

		switch ( $source ) {
			case 'options':
				$available = $field_def['options'] ?? array();
				foreach ( $raw_value as $selected_id ) {
					foreach ( $available as $opt ) {
						// Handle optgroup structure.
						if ( isset( $opt['options'] ) ) {
							foreach ( $opt['options'] as $child ) {
								if ( (string) $child['id'] === (string) $selected_id ) {
									$options[] = array(
										'id'   => $child['id'],
										'text' => $child['text'],
									);
								}
							}
						} elseif ( (string) $opt['id'] === (string) $selected_id ) {
							$options[] = array(
								'id'   => $opt['id'],
								'text' => $opt['text'],
							);
						}
					}
				}
				break;

			case 'post':
			case 'product':
				foreach ( $raw_value as $id ) {
					$post_obj = get_post( $id );
					if ( ! empty( $post_obj ) ) {
						$options[] = array(
							'id'   => (string) $post_obj->ID,
							'text' => $post_obj->post_title,
						);
					}
				}
				break;

			case 'user':
				foreach ( $raw_value as $id ) {
					$user = get_user_by( 'ID', $id );
					if ( $user ) {
						$options[] = array(
							'id'   => (string) $user->ID,
							'text' => $user->display_name,
						);
					}
				}
				break;
		}

		return $options;
	}

	/**
	 * Resolve product data for products_selector fields.
	 *
	 * Used when building deferred rendering data. Traverses the layout's field
	 * definitions and resolves product details (ID, title, image, price) for
	 * every products_selector field that has a non-empty saved value.
	 *
	 * @since 2.3
	 *
	 * @param array<string, mixed> $fields Layout field definitions.
	 * @param array<string, mixed> $values Saved values for the layout row.
	 *
	 * @return array<string, array<int, array{id: int, title: string, image: string, price: string}>>
	 */
	public function resolve_product_options( array $fields, array $values ): array {
		return $this->walk_fields_of_type( $fields, $values, 'products_selector', array( $this, 'resolve_single_product' ) );
	}

	/**
	 * Resolve a single products_selector field's selected values to product data.
	 *
	 * @since 2.3
	 *
	 * @param array<string, mixed> $field_def The field definition.
	 * @param mixed                $raw_value The saved value (comma-separated IDs string).
	 *
	 * @return array<int, array{id: int, title: string, image: string, price: string, type: string, edit_url: string}>|null
	 */
	private function resolve_single_product( array $field_def, $raw_value ): ?array {
		if ( empty( $raw_value ) ) {
			return null;
		}

		$ids     = explode( ',', (string) $raw_value );
		$options = array();

		foreach ( $ids as $id ) {
			$id = trim( $id );
			if ( '' === $id ) {
				continue;
			}

			$product = wc_get_product( $id );
			if ( $product ) {
				$edit_url = $product->is_type( 'variation' )
					? get_edit_post_link( $product->get_parent_id(), 'raw' )
					: get_edit_post_link( $product->get_id(), 'raw' );

				$image = wp_get_attachment_image_url( (int) $product->get_image_id(), 'thumbnail' );

				$options[] = array(
					'id'       => $product->get_id(),
					'title'    => $product->get_name(),
					'image'    => $image ? $image : '',
					'price'    => $product->get_price_html(),
					'type'     => $product->get_type(),
					'edit_url' => $edit_url ? $edit_url : '',
				);
			}
		}

		return $options;
	}

	/**
	 * Resolve review data for reviews_selector fields.
	 *
	 * Used when building deferred rendering data. Traverses the layout's field
	 * definitions and resolves review details (ID, author, content, rating,
	 * product info) for every reviews_selector field with a non-empty saved value.
	 *
	 * @since 2.3
	 *
	 * @param array<string, mixed> $fields Layout field definitions.
	 * @param array<string, mixed> $values Saved values for the layout row.
	 *
	 * @return array<string, array<int, array{id: string, author: string, content: string, rating: string, product_id: string, product_name: string}>>
	 */
	public function resolve_review_options( array $fields, array $values ): array {
		return $this->walk_fields_of_type( $fields, $values, 'reviews_selector', array( $this, 'resolve_single_review' ) );
	}

	/**
	 * Resolve a single reviews_selector field's selected values to review data.
	 *
	 * @since 2.3
	 *
	 * @param array<string, mixed> $field_def The field definition.
	 * @param mixed                $raw_value The saved value (comma-separated IDs string or array).
	 *
	 * @return array<int, array{id: string, author: string, content: string, rating: string, product_id: string, product_name: string}>|null
	 */
	private function resolve_single_review( array $field_def, $raw_value ): ?array {
		if ( empty( $raw_value ) ) {
			return null;
		}

		// reviews_selector stores comma-separated IDs as a string.
		$ids     = is_array( $raw_value ) ? $raw_value : explode( ',', (string) $raw_value );
		$options = array();

		foreach ( $ids as $id ) {
			$id = trim( (string) $id );
			if ( '' === $id ) {
				continue;
			}

			$comment = get_comment( $id );
			if ( ! $comment ) {
				continue;
			}

			$rating       = get_comment_meta( (int) $comment->comment_ID, 'rating', true );
			$product_post = get_post( (int) $comment->comment_post_ID );

			$options[] = array(
				'id'           => $comment->comment_ID,
				'author'       => $comment->comment_author,
				'content'      => $comment->comment_content,
				'rating'       => (string) $rating,
				'product_id'   => $comment->comment_post_ID,
				'product_name' => $product_post ? $product_post->post_title : '',
			);
		}

		return $options;
	}

	/**
	 * Build the full deferred rendering data for a single layout row.
	 *
	 * Combines the saved values with resolved option maps for select_ajax,
	 * products_selector, and reviews_selector fields. The returned array
	 * is JSON-encoded into a `data-fields-json` attribute on the row
	 * element for client-side hydration.
	 *
	 * @since 2.2.5
	 *
	 * @param array<string, mixed> $layout_fields Layout field definitions.
	 * @param array<string, mixed> $values        Saved values for the layout row.
	 *
	 * @return array{values: array<string, mixed>, select_options: array<string, mixed>, product_options: array<string, mixed>, review_options: array<string, mixed>}
	 */
	public function build_deferred_data( array $layout_fields, array $values ): array {
		return array(
			'values'          => $values,
			'select_options'  => $this->resolve_select_options( $layout_fields, $values ),
			'product_options' => $this->resolve_product_options( $layout_fields, $values ),
			'review_options'  => $this->resolve_review_options( $layout_fields, $values ),
		);
	}

	/**
	 * Determine the active row index for accordion display.
	 *
	 * If a `campaign_id` query parameter is present and matches a row's
	 * `flexible_id`, that row's index is returned. Otherwise defaults to 0.
	 *
	 * @since 2.2.5
	 *
	 * @param array<int, array<string, mixed>> $values All saved rows.
	 *
	 * @return int The zero-based index of the active row.
	 */
	public function get_active_index( array $values ): int {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only display logic, no state mutation.
		$campaign_id = isset( $_GET['campaign_id'] ) ? sanitize_text_field( wp_unslash( $_GET['campaign_id'] ) ) : '';

		if ( '' === $campaign_id ) {
			return 0;
		}

		foreach ( $values as $index => $row ) {
			if ( isset( $row['flexible_id'] ) && $row['flexible_id'] === $campaign_id ) {
				return (int) $index;
			}
		}

		return 0;
	}

	/**
	 * Determine if a row should use deferred rendering.
	 *
	 * A row is deferred when:
	 * - Accordion mode is enabled
	 * - It is NOT the clone template
	 * - It is NOT the active index
	 *
	 * @since 2.2.5
	 *
	 * @param int  $row_index     The row's index in the values array.
	 * @param int  $active_index  The currently active row index.
	 * @param bool $has_accordion Whether accordion mode is enabled.
	 * @param bool $is_clone      Whether this is a clone template row.
	 *
	 * @return bool
	 */
	public function is_row_deferred( int $row_index, int $active_index, bool $has_accordion, bool $is_clone ): bool {
		// Clone templates are never deferred — they serve as the JS template.
		if ( $is_clone ) {
			return false;
		}

		// Without accordion, all rows are visible — no deferral needed.
		if ( ! $has_accordion ) {
			return false;
		}

		// The active row is always rendered fully.
		return $row_index !== $active_index;
	}
}
