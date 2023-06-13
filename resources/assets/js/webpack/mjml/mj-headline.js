
const { BodyComponent } = require('mjml-core');
const { registerDependencies } = require('mjml-validator');

registerDependencies({
  'mj-headline': [],
  'mj-column': ['mj-headline'],
});

class MjHeadline extends BodyComponent {
    static componentName = 'mj-headline';
    static endingTag = true;

    static allowedAttributes = {
        'background-color': 'color',
        'color': 'color',
        'font-family': 'string',
        'font-size': 'unit(px)',
        'font-style': 'string',
        'font-weight': 'string',
        'letter-spacing': 'unitWithNegative(px,em)',
        'line-height': 'unit(px,%,)',
        'padding-bottom': 'unit(px,%)',
        'padding-left': 'unit(px,%)',
        'padding-right': 'unit(px,%)',
        'padding-top': 'unit(px,%)',
        'padding': 'unit(px,%){1,4}',
        'text-decoration': 'string',
        'text-transform': 'string',
        'icon': 'string',
    };

    static defaultAttributes = {
        'background-color': '',
        'color': '#000000',
        'font-family': 'Ubuntu, Helvetica, Arial, sans-serif',
        'font-size': '13px',
        'line-height': '1',
        'padding': '25px',
        'width': '100%',
    };

    getHeadlineIcon() {
        const icons = {
            'fa-certificate': true,
            'fa-check-circle': true,
            'fa-check': true,
            'fa-cogs': true,
            'fa-exclamation-circle': true,
            'fa-exclamation-triangle': true,
            'fa-file-text': true,
            'fa-frown': true,
            'fa-lock': true,
            'fa-pencil': true,
            'fa-question-circle': true,
            'fa-smile-o': true,
            'fa-thumbs-up': true,
            'fa-times-circle': true,
            'fa-unlock': true,
            'fa-dark-certificate': true,
            'fa-dark-check-circle': true,
            'fa-dark-check': true,
            'fa-dark-cogs': true,
            'fa-dark-exclamation-circle': true,
            'fa-dark-exclamation-triangle': true,
            'fa-dark-file-text': true,
            'fa-dark-frown': true,
            'fa-dark-lock': true,
            'fa-dark-pencil': true,
            'fa-dark-question-circle': true,
            'fa-dark-smile-o': true,
            'fa-dark-thumbs-up': true,
            'fa-dark-times-circle': true,
            'fa-dark-unlock': true,
        };

        let icon = this.getAttribute('icon');
        return icon && icons[icon] ? icon : '';
    }

    getStyles() {
        return {
            table: {
                'background-color': this.getAttribute('background-color'),
                'padding': this.getAttribute('padding'),
                'width': this.getAttribute('width'),
            },
            text: {
                'font-family': this.getAttribute('font-family'),
                'font-size': this.getAttribute('font-size'),
                'font-style': this.getAttribute('font-style'),
                'font-weight': this.getAttribute('font-weight'),
                'letter-spacing': this.getAttribute('letter-spacing'),
                'line-height': this.getAttribute('line-height'),
                'text-decoration': this.getAttribute('text-decoration'),
                'text-transform': this.getAttribute('text-transform'),
                'color': this.getAttribute('color'),
            },
        }
    }

    renderIcon(icon) {
        return icon && `
            <td style="vertical-align:middle" width="95">
                <img src="https://cdn.givecloud.co/static/notifications/${icon}.png" alt="" style="width:70px;height:auto">
            </td>
        `;
    }

    render() {

        return this.renderMJML(`
            <mj-table cellpadding="0" cellspacing="0" ${this.htmlAttributes({ style: 'table' })}>
                <tr>
                    ${this.renderIcon(this.getHeadlineIcon())}
                    <td style="vertical-align:middle">
                        <mj-text ${this.htmlAttributes({ style: 'text' })}>
                            ${this.getContent()}
                        </mj-text>
                    </td>
                </tr>
            </mj-table>
        `);
    }
}

module.exports = MjHeadline;
