(function (wp) {
  var el = wp.element.createElement;
  var __ = wp.i18n.__;
  var InspectorControls = wp.editor ? wp.editor.InspectorControls : wp.blocks.InspectorControls;
  var Components = wp.components ? wp.components : wp.blocks;
  var ServerSideRender = Components.ServerSideRender;
  var PanelBody = Components.PanelBody;
  var SelectControl = Components.SelectControl;
  // Visit https://wordpress.org/gutenberg/handbook/block-api/ to learn about Block API
  wp.blocks.registerBlockType('wpme/wpme-lumens-block', {
    title: __('Lumens'),
    description: __('Attach your Lumens'),
    category: 'widgets',
    icon: 'media-spreadsheet',
    supportHTML: false,
    attributes: {
      id: {
        type: 'integer'
      },
    },
    supports: {
      align: false,
      className: false,
      anchor: false,
      customClassName: false,
      html: false,
    },
    transformss: {
      from: [
        {
          type: 'shortcode',
          tag: GenooVars.SHORTCODE.LUMENS,
          attributes: {
            id: {
              type: 'integer',
              shortcode: function (named) {
                return named.id ? named.id : '';
              },
            },
          }
        }
      ]
    },

    /**
     *
     * @param props
     */
    edit: function (props) {
      if (!GenooVars || !GenooVars.EDITOR) {
        return;
      }
      return [
        el(
          InspectorControls,
          { key: 'inspector' },
          el(PanelBody,
            { className: 'blocks-font-size', title: __('Settings') },
            el(SelectControl,
              {
                type: 'integer',
                label: __('Lumen'),
                value: props.attributes.id,
                options: GenooVars.EDITOR.Survey,
                onChange: function (id) {
                  props.setAttributes({ id: id });
                },
              }
            )
          )
        ),
        el(ServerSideRender,
          {
            block: 'wpme/wpme-lumens-block',
            attributes: props.attributes,
          }
        ),
      ];
    },

    /**
     * @returns {*}
     */
    save: function () {
      return null
    }

  });
})(
  window.wp
);
