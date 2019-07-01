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
  var TextareaControl = Components.TextareaControl;
  // Visit https://wordpress.org/gutenberg/handbook/block-api/ to learn about Block API
  wp.blocks.registerBlockType('wpme/wpme-form-block', {
    title: __('Form'),
    description: __('Attach your Form'),
    category: 'widgets',
    icon: 'feedback',
    supportHTML: false,
    attributes: {
      id: {
        type: 'integer'
      },
      theme: {
        type: 'string'
      },
      confirmation: {
        type: 'bool'
      },
      msgSuccess: {
        type: 'string'
      },
      msgFail: {
        type: 'string'
      }
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
          tag: GenooVars.SHORTCODE.FORM,
          attributes: {
            id: {
              type: 'integer',
              shortcode: function (named) {
                return named.id ? named.id : '';
              },
            },
            theme: {
              type: 'string',
              shortcode: function (named) {
                return named.theme ? named.theme : '';
              },
            },
            confirmation: {
              type: 'bool',
              shortcode: function (named) {
                return named.confirmation ? named.confirmation : 'false';
              },
            },
            msgSuccess: {
              type: 'string',
              shortcode: function (named) {
                return named.msgSuccess ? named.msgSuccess : '';
              },
            },
            msgFail: {
              type: 'string',
              shortcode: function (named) {
                return named.msgFail ? named.msgFail : '';
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
      var messagesWrapper = !props.attributes.confirmation ? Disabled : 'div';
      return [
        el(
          InspectorControls,
          { key: 'inspector' },
          el(PanelBody,
            { className: 'blocks-font-size', title: __('Settings') },
            el(SelectControl,
              {
                type: 'integer',
                label: __('Form'),
                value: props.attributes.id,
                options: GenooVars.EDITOR.Form,
                onChange: function (id) {
                  props.setAttributes({ id: id });
                },
              }
            ),
            el(SelectControl,
              {
                type: 'string',
                label: __('Theme'),
                value: props.attributes.theme,
                options: GenooVars.EDITOR.Themes,
                onChange: function (theme) {
                  props.setAttributes({ theme: theme });
                },
              }
            ),
            el(ToggleControl,
              {
                type: 'boolean',
                label: __('Display confirmation message inline?'),
                checked: props.attributes.confirmation,
                onChange: function () {
                  props.setAttributes({ confirmation: !props.attributes.confirmation });
                },
              }
            ),
            el(messagesWrapper,
              {},
              el(TextareaControl,
                {
                  type: 'string',
                  label: __('Form success message'),
                  value: props.attributes.msgSuccess,
                  help: __('number of seconds to take for the CTA to appear'),
                  onChange: function (msgSuccess) {
                    props.setAttributes({ msgSuccess: msgSuccess });
                  },
                }
              ),
              el(TextareaControl,
                {
                  type: 'string',
                  label: __('Form error message'),
                  value: props.attributes.msgFail,
                  help: __('number of seconds to take for the CTA to appear'),
                  onChange: function (msgFail) {
                    props.setAttributes({ msgFail: msgFail });
                  },
                }
              )
            )
          )
        ),
        el(ServerSideRender,
          {
            block: 'wpme/wpme-form-block',
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
