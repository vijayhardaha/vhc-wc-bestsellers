# CHANGELOG

## Version 1.1.0 - September 17, 2023

- Resolved an issue with RankMath SEO for the bestseller page.

## Version 1.0.3 - November 18, 2022

- Optimized code and removed unused functions for environment checks.

## Version 1.0.2 - October 11, 2022

- Added cache on query products using transient.
- Improved shortcode query by using `WP_Query`` instead of an array of product objects.
- Removed WooCommerce archive description hook customization that was used to fix the archive description rendering in the initial release.

## Version 1.0.1 - October 11, 2022

- Added a function with `woocommerce_shop_page_id_for_archive_description` filter hook to fix the archive description rendering on custom bestseller archive pages.

## Version 1.0.0 - September 01, 2022

- Initial release of the plugin.
