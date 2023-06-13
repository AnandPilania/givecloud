import fixLI from './fixLI';
import formatSpecialFields from './formatSpecialFields';
import imageBrowser from './imageBrowser';
import menu from './menu';
import table from './table';

export default function() {
    formatSpecialFields();
    menu();
    fixLI();
    table.init();
    imageBrowser.init();
}
