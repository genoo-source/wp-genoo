(function (wp) {
  var el = wp.element.createElement;
  var __ = wp.i18n.__;
  var InspectorControls = wp.editor ? wp.editor.InspectorControls : wp.blocks.InspectorControls;
  var Components = wp.components ? wp.components : wp.blocks;
  var ServerSideRender = Components.ServerSideRender;
  var Disabled = Components.Disabled;
  var PanelBody = Components.PanelBody;
  var ToggleControl = Components.ToggleControl;
  var SelectControl = Components.SelectControl;
  var TextControl = Components.TextControl;
  // Visit https://wordpress.org/gutenberg/handbook/block-api/ to learn about Block API
  wp.blocks.registerBlockType('wpme/wpme-cta-block', {
    title: __('CTA'),
    description: __('Attach your CTA'),
    category: 'widgets',
    icon: 'migrate',
    supportHTML: false,
    attributes: {
      id: {
        type: 'string'
      },
      align: {
        type: 'string'
      },
      hasTime: {
        type: 'bool'
      },
      time: {
        type: 'integer'
      }
    },
    supports: {
      align: true,
      className: false,
      anchor: false,
      customClassName: false,
      html: false,
    },
    transforms: {
      from: [
        {
          type: 'shortcode',
          tag: GenooVars.SHORTCODE.CTA,
          attributes: {
            id: {
              type: 'string',
              shortcode: function (named) {
                return named.id ? named.id : '';
              },
            },
            align: {
              type: 'string',
              shortcode: function (named) {
                return named.align ? named.align : 'none';
              },
            },
            hasTime: {
              type: 'bool',
              shortcode: function (named) {
                return named.hasTime ? named.hasTime : 'false';
              },
            },
            time: {
              type: 'integer',
              shortcode: function (named) {
                return named.time ? named.time : '';
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
      var timeWrapper = !props.attributes.hasTime ? Disabled : 'div';
      return [
        el(
          InspectorControls,
          { key: 'inspector' },
          el(PanelBody,
            { className: 'blocks-font-size', title: __('Settings') },
            el(SelectControl,
              {
                type: 'string',
                label: __('CTA'),
                value: props.attributes.id,
                options: GenooVars.EDITOR.CTA,
                onChange: function (id) {
                  props.setAttributes({ id: id });
                },
              }
            ),
            el(ToggleControl,
              {
                type: 'number',
                label: __('Allow CTA to appear after a time interval?'),
                checked: props.attributes.hasTime,
                onChange: function () {
                  props.setAttributes({ hasTime: !props.attributes.hasTime });
                },
              }
            ),
            el(timeWrapper,
              {},
              el(TextControl,
                {
                  type: 'number',
                  label: __('CTA appearance interval'),
                  value: props.attributes.time,
                  help: __('number of seconds to take for the CTA to appear'),
                  onChange: function (time) {
                    props.setAttributes({ time: time });
                  },
                }
              )
            )
            // CTA appearance interval
          )
        ),
        el(ServerSideRender,
          {
            block: 'wpme/wpme-cta-block',
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
