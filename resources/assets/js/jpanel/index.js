import avatar from './avatar'
import charts from './charts';
import contentTemplates from './contentTemplates';
import contributions from './contributions'
import createChargeBeeCheckout from "./createChargeBeeCheckout";
import downloads from './downloads';
import dpdonor from './dpdonor';
import dpgift from './dpgift';
import dsSponsor from './dsSponsor';
import filtersToQueryString from './filtersToQueryString';
import hooks from './hooks';
import hotglue from './hotglue/connector';
import images from './images';
import importDonors from './importDonors';
import importer from './flatfile/importer'
import init from './init';
import list from './list';
import openCustomerPortal from './openCustomerPortal';
import orders from './orders';
import page from './page';
import payment_group from './payment_group';
import post from './post';
import posttype from './posttype';
import previewImage from './previewImage';
import product from './product';
import promo from './promo';
import segment from './segment';
import settings from './settings';
import supporters from './supporters';
import t2gNumbers from './t2gNumbers';
import taxReceipt from './taxReceipt';
import templates from './templates';
import toFixed from './toFixed';
import tribute from './tribute';
import ui from './ui';
import user from './user';
import util from './util';

const j = {
    avatar,
    charts,
    contentTemplates,
    contributions,
    createChargeBeeCheckout,
    downloads,
    dpdonor,
    dpgift,
    dsSponsor,
    filtersToQueryString,
    hooks,
    hotglue,
    images,
    importDonors,
    importer,
    init,
    list,
    openCustomerPortal,
    orders,
    page,
    payment_group,
    post,
    posttype,
    previewImage,
    product,
    promo,
    segment,
    settings,
    supporters,
    t2gNumbers,
    taxReceipt,
    templates,
    toFixed,
    tribute,
    ui,
    user,
    util,
};

window.j = j;

export default j;
