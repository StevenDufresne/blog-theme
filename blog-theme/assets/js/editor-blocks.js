(function (blocks, element, i18n, serverSideRender) {
  const el = element.createElement;
  const ServerSideRender = serverSideRender.default || serverSideRender;
  const __ = i18n.__;

  function registerLabNotesBlock(name, title) {
    blocks.registerBlockType(name, {
      apiVersion: 3,
      title,
      category: "theme",
      icon: "editor-code",
      supports: {
        html: false
      },
      edit() {
        return el(ServerSideRender, {
          block: name
        });
      },
      save() {
        return null;
      }
    });
  }

  registerLabNotesBlock("lab-notes/sidebar", __("Lab Notes Sidebar", "lab-notes"));
  registerLabNotesBlock("lab-notes/index-content", __("Lab Notes Index Content", "lab-notes"));
  registerLabNotesBlock("lab-notes/single-content", __("Lab Notes Single Content", "lab-notes"));
  registerLabNotesBlock("lab-notes/archive-content", __("Lab Notes Archive Content", "lab-notes"));
})(window.wp.blocks, window.wp.element, window.wp.i18n, window.wp.serverSideRender);
