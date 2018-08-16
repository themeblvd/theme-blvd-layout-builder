import wp from 'wp';
import l10n from 'l10n';

const { MenuItem } = wp.components;
const { addFilter } = wp.hooks;

const uniqueName = 'theme-blvd-layout-builder/layout-editor';

const layoutEditor = menuItems => [
  ...menuItems,
  <MenuItem
    key={uniqueName}
    icon={false}
    isSelected={false}
    onClick={() => {
      window.location.href = l10n.editLink;
    }}
  >
    {l10n.edit}
  </MenuItem>
];

addFilter('editPost.MoreMenu.editor', uniqueName, layoutEditor);
