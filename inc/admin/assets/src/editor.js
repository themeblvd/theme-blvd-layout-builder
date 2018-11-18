import wp from 'wp';
import l10n from 'l10n';
import BuildingIcon from './utils/building-icon.js';

const { editText, editLink, redirectText } = l10n;
const { Component, Fragment } = wp.element;
const { registerPlugin } = wp.plugins;
const { Fill, IconButton, MenuItem } = wp.components;

class LayoutEditor extends Component {
  state = {
    isRedirecting: false
  };

  componentDidMount() {
    window.addEventListener('beforeunload', this.handleBeforeUnload);
  }

  componentWillUnmount() {
    window.removeEventListener('beforeunload', this.handleBeforeUnload);
  }

  handleBeforeUnload = () => {
    this.timeout = setTimeout(() => {
      this.setState({ isRedirecting: false });
    }, 1000);
  };

  handleClick = event => {
    event.preventDefault();
    this.setState({ isRedirecting: true });
    const url = new URL(window.location.href);
    const postId = url.searchParams.get('post');
    window.location.href = `${editLink}&post_id=${postId}`;
  };

  render() {
    const { isRedirecting } = this.state;

    return (
      <Fragment>
        <Fill name="PinnedPlugins">
          <IconButton
            onClick={this.handleClick}
            className={this.state.isRedirecting ? 'is-busy' : ''}
            tooltip={editText}
          >
            <BuildingIcon />
          </IconButton>
        </Fill>
        <Fill name="ToolsMoreMenuGroup">
          <MenuItem icon="external" onClick={this.handleClick}>
            {this.state.isRedirecting ? redirectText : editText}
          </MenuItem>
        </Fill>
      </Fragment>
    );
  }
}

registerPlugin('theme-blvd-layout-builder', {
  render: LayoutEditor
});
