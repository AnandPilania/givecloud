
export default function(sectionType, params) {
    var cb_callbacks = {
        close: function(){
            window.location = "/jpanel/chargebee/updated";
        }
    };
    var sectionTypes = window.Chargebee.getPortalSections();
    if (sectionTypes[sectionType]) {
        return window.Chargebee.getInstance().createChargebeePortal().open(cb_callbacks, {
            sectionType: sectionTypes[sectionType],
            params: params || null
        });
    } else {
        return window.Chargebee.getInstance().createChargebeePortal().open(cb_callbacks);
    }
}
