(function ($, api) {
	'use strict';

	var baseEmbed = api.Panel.prototype.embed;
	var baseAttachEvents = api.Panel.prototype.attachEvents;
	var baseIsContextuallyActive = api.Panel.prototype.isContextuallyActive;

	api.Panel = api.Panel.extend({
		embed: function () {
			baseEmbed.call(this);

			if (this.params.type !== 'aihl_nested_panel' || !this.params.panel) {
				return;
			}

			$('#sub-accordion-panel-' + this.params.panel).append(this.headContainer);
		},

		attachEvents: function () {
			baseAttachEvents.call(this);

			if (this.params.type !== 'aihl_nested_panel' || !this.params.panel) {
				return;
			}

			var panel = this;
			panel.expanded.bind(function (expanded) {
				var parent = api.panel(panel.params.panel);
				if (parent) {
					parent.contentContainer.toggleClass('aihl-current-panel-parent', expanded);
				}
			});

			panel.container.find('.customize-panel-back')
				.off('click keydown')
				.on('click keydown', function (event) {
					if (api.utils.isKeydownButNotEnterEvent(event)) {
						return;
					}
					event.preventDefault();
					var parent = api.panel(panel.params.panel);
					if (parent) {
						parent.expand();
					}
				});
		},

		isContextuallyActive: function () {
			if (this.params.type !== 'aihl_nested_panel') {
				return baseIsContextuallyActive.call(this);
			}

			var panelId = this.id;
			var children = this._children('panel', 'section');
			api.panel.each(function (child) {
				if (child.params.panel === panelId) {
					children.push(child);
				}
			});

			return children.some(function (child) {
				return child.active() && child.isContextuallyActive();
			});
		}
	});

	api.bind('pane-contents-reflowed', function () {
		var panels = [];
		api.panel.each(function (panel) {
			if (panel.params.type === 'aihl_nested_panel' && panel.params.panel) {
				panels.push(panel);
			}
		});
		panels.sort(api.utils.prioritySort).reverse().forEach(function (panel) {
			var parent = $('#sub-accordion-panel-' + panel.params.panel);
			parent.children('.panel-meta').after(panel.headContainer);
		});
	});
})(jQuery, wp.customize);
