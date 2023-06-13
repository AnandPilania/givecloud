
## Schemas

Global/Theme settings schema is found in settings_schema.json

Sections have their own settings schema which uses the same format as settings_schema.json. The sections define the schema with the Liquid {% schema %} tag. Each section can have a single schema tag. The schema tag can be placed anywhere within a section file but cannot be nested inside another Liquid tag.

Template meta (i.e. custom fields for products, pages, etc) uses the same format as settings_schema.json. The meta is defined with the Liquid {% meta %} tag. Each template can have a single meta tag. The meta tag can be placed anywhere within a template file but cannot be nested inside another Liquid tag.


## Alternate/custom templates

Alternate templates can be created using dot notation. For example if we wanted to create a alternate version of `product.liquid` for One-Page checkout we'd create a template called `product.one-page.liquid`.


## Reference resources

https://shopify.github.io/liquid/
https://help.shopify.com/themes/liquid/

