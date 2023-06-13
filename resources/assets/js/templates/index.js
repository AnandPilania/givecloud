
import _ from 'lodash';
import contentTemplatesModalTmpl from './app/contentTemplatesModalTmpl.html';
import contentTemplatesThumbTmpl from './app/contentTemplatesThumbTmpl.html';
import currencyDropdownTmpl from './pos/currencyDropdownTmpl.html';
import donorModalTmpl from './app/donorModalTmpl.html';
import fieldsTmpl from './products/fieldsTmpl.html';
import filePreviewTmpl from './app/filePreviewTmpl.html';
import filesModalTmpl from './app/filesModalTmpl.html';
import fileTmpl from './app/fileTmpl.html';
import giftModalTmpl from './app/giftModalTmpl.html';
import imagePreviewTmpl from './app/imagePreviewTmpl.html';
import imagesModalTmpl from './app/imagesModalTmpl.html';
import imageThumbTmpl from './app/imageThumbTmpl.html';
import paginationTmpl from './app/paginationTmpl.html';
import paymentOptionsTmpl from './sponsorships/paymentOptionsTmpl.html';
import sponsorModalTmpl from './app/sponsorModalTmpl.html';
import taxReceiptModalTmpl from './app/taxReceiptModalTmpl.html';
import timelinifyModalTmpl from './sponsorships/timelinifyModalTmpl.html';
import transientLogTmpl from './app/transientLogTmpl.html';
import tributeModalTmpl from './app/tributeModalTmpl.html';
import txnModalTmpl from './app/txnModalTmpl.html';
import variantModalTmpl from './products/variantModalTmpl.html';
import variantRowTmpl from './products/variantRowTmpl.html';

function compileTemplate(html) {
    return _.template(html.trim());
}

export default {
    contentTemplatesModalTmpl: compileTemplate(contentTemplatesModalTmpl),
    contentTemplatesThumbTmpl: compileTemplate(contentTemplatesThumbTmpl),
    currencyDropdownTmpl: compileTemplate(currencyDropdownTmpl),
    donorModalTmpl: compileTemplate(donorModalTmpl),
    fieldsTmpl: compileTemplate(fieldsTmpl),
    filePreviewTmpl: compileTemplate(filePreviewTmpl),
    filesModalTmpl: compileTemplate(filesModalTmpl),
    fileTmpl: compileTemplate(fileTmpl),
    giftModalTmpl: compileTemplate(giftModalTmpl),
    imagePreviewTmpl: compileTemplate(imagePreviewTmpl),
    imagesModalTmpl: compileTemplate(imagesModalTmpl),
    imageThumbTmpl: compileTemplate(imageThumbTmpl),
    paginationTmpl: compileTemplate(paginationTmpl),
    paymentOptionsTmpl: compileTemplate(paymentOptionsTmpl),
    sponsorModalTmpl: compileTemplate(sponsorModalTmpl),
    taxReceiptModalTmpl: compileTemplate(taxReceiptModalTmpl),
    timelinifyModalTmpl: compileTemplate(timelinifyModalTmpl),
    transientLogTmpl: compileTemplate(transientLogTmpl),
    tributeModalTmpl: compileTemplate(tributeModalTmpl),
    txnModalTmpl: compileTemplate(txnModalTmpl),
    variantModalTmpl: compileTemplate(variantModalTmpl),
    variantRowTmpl: compileTemplate(variantRowTmpl),
};
