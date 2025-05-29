/**
 * Team Showcase Gutenberg Block
 */
;((blocks, element, components, editor) => {
    var el = element.createElement
    var InspectorControls = editor.InspectorControls
    var PanelBody = components.PanelBody
    var RangeControl = components.RangeControl
    var ToggleControl = components.ToggleControl
  
    blocks.registerBlockType("team-showcase/team-grid", {
      title: "Team Showcase",
      icon: "groups",
      category: "widgets",
      attributes: {
        itemsPerPage: {
          type: "number",
          default: 6,
        },
        showSearch: {
          type: "boolean",
          default: true,
        },
        showDepartmentFilter: {
          type: "boolean",
          default: true,
        },
      },
  
      edit: (props) => {
        var attributes = props.attributes
  
        return el("div", { className: props.className }, [
          // Inspector controls for block settings
          el(
            InspectorControls,
            { key: "inspector" },
            el(
              PanelBody,
              {
                title: "Team Showcase Settings",
                initialOpen: true,
              },
              [
                el(RangeControl, {
                  label: "Items Per Page",
                  value: attributes.itemsPerPage,
                  onChange: (value) => {
                    props.setAttributes({ itemsPerPage: value })
                  },
                  min: 1,
                  max: 24,
                }),
                el(ToggleControl, {
                  label: "Show Search",
                  checked: attributes.showSearch,
                  onChange: () => {
                    props.setAttributes({ showSearch: !attributes.showSearch })
                  },
                }),
                el(ToggleControl, {
                  label: "Show Department Filter",
                  checked: attributes.showDepartmentFilter,
                  onChange: () => {
                    props.setAttributes({ showDepartmentFilter: !attributes.showDepartmentFilter })
                  },
                }),
              ],
            ),
          ),
  
          // Block preview
          el("div", { className: "team-showcase-block-preview" }, [
            el("div", { className: "team-showcase-block-icon" }, el("span", { className: "dashicons dashicons-groups" })),
            el("div", { className: "team-showcase-block-title" }, "Team Showcase"),
            el(
              "div",
              { className: "team-showcase-block-description" },
              "Displays your team members in a responsive grid with filtering and pagination.",
            ),
            el("div", { className: "team-showcase-block-settings" }, [
              el("span", {}, "Items per page: " + attributes.itemsPerPage),
              el("span", {}, "Search: " + (attributes.showSearch ? "Enabled" : "Disabled")),
              el("span", {}, "Department filter: " + (attributes.showDepartmentFilter ? "Enabled" : "Disabled")),
            ]),
          ]),
        ])
      },
  
      save: () => {
        // Dynamic block, rendering is handled by PHP
        return null
      },
    })
  })(window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor)
  