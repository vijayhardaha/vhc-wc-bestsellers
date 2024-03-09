# CHANGELOG

## Version 1.1.2 - Mar 09, 2024

- Move plugin action link before the deactivate link.
- Write license name in valid SPDX expression.
- Bump dependencies to the latest versions.

## Version 1.1.1 - Sep 20, 2023

- Improved the RankMath SEO title and description filter callback logic for better code organization and future-proofing. This enhancement involved creating new variables, `$description_setting` and `$title_setting`, to store settings data, replacing the direct use of `$description` and `$title`. This change enhances code clarity and reduces the risk of potential issues in the future.

## Version 1.1.0 - Sep 17, 2023

- Resolved an issue with RankMath SEO for the bestseller page.

## Version 1.0.3 - Nov 18, 2022

- Optimized code and removed unused functions for environment checks.

## Version 1.0.2 - Oct 11, 2022

- Added cache on query products using transient.
- Improved shortcode query by using `WP_Query`` instead of an array of product objects.
- Removed WooCommerce archive description hook customization that was used to fix the archive description rendering in the initial release.

## Version 1.0.1 - Oct 11, 2022

- Added a function with `woocommerce_shop_page_id_for_archive_description` filter hook to fix the archive description rendering on custom bestseller archive pages.

## Version 1.0.0 - Sep 01, 2022

- Initial release of the plugin.
