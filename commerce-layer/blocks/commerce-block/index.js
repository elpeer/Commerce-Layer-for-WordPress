/**
 * Commerce Block
 * Gutenberg Block for Commerce Layer
 */

(function(blocks, element, blockEditor, components) {
    var el = element.createElement;
    var useBlockProps = blockEditor.useBlockProps;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var TextControl = components.TextControl;

    blocks.registerBlockType('commerce-layer/purchase-card', {
        title: 'Commerce Layer',
        description: 'הצג רכיב רכישה למוצר',
        icon: 'cart',
        category: 'widgets',
        attributes: {
            postId: {
                type: 'number',
                default: 0
            }
        },

        edit: function(props) {
            var blockProps = useBlockProps();
            var postId = props.attributes.postId;

            return el('div', blockProps,
                el(InspectorControls, {},
                    el(PanelBody, { title: 'הגדרות', initialOpen: true },
                        el(TextControl, {
                            label: 'מזהה פוסט (אופציונלי)',
                            help: 'השאר ריק להשתמש בפוסט הנוכחי',
                            type: 'number',
                            value: postId || '',
                            onChange: function(value) {
                                props.setAttributes({ postId: parseInt(value) || 0 });
                            }
                        })
                    )
                ),
                el('div', { className: 'cl-block-placeholder' },
                    el('span', { className: 'dashicons dashicons-cart' }),
                    el('p', {}, postId ? 'רכיב קומרס - פוסט #' + postId : 'רכיב קומרס (הפוסט הנוכחי)')
                )
            );
        },

        save: function() {
            // Render callback on server
            return null;
        }
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.blockEditor,
    window.wp.components
);
