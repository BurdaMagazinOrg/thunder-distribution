# Thunder AMP Subtheme

This Thunder AMP theme includes config and templates to use AMP with the
Thunder distribution.
If you install this theme, two things will happen:
1. During the installation, the directoy `config/optional` of this theme will be read
and included in the config. This configures the view modes for article, paragraphs, media and blocks.
2. The thunder profile will automatically configure
the AMP settings (to be found in "Configuration/Content authoring/AMP Configuration")
and set this theme as the AMP theme.

If you already created the view mode AMP for the article, you will have to manually set paragraphs
to `Rendered entity` with the view mode `AMP`.

Please refer to the README at the root of amptheme for full installation
instructions to get your site ready for AMP.

To create your own custom subtheme, refer to the AMP subtheme example.
You can either use `amptheme` as the base and include configs of this theme as you whish,
or set `thunder_amp` as the base, in which case you get all configs set by this theme
and the profile automatically.
In either case, you have to set your subtheme at `/admin/config/content/amp` manually.

Make sure to follow guidelines at https://www.ampproject.org/ on allowed styles
and markup in order to have valid HTML. Please note that CSS and JS added in a
libraries.yml file will not be loaded on AMP-enabled pages.