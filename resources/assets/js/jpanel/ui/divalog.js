/* globals j */

import $ from 'jquery';

export default {
    show:function(str,url,params){
        var wrp = $('<div id="dlg_'+str+'" class="divalog"><div class="mask"></div><div class="box"><div class="txt"><img src="/jpanel/assets/images/working.gif" /></div><img src="/jpanel/assets/images/d_close.png" class="close" alt="Close" onclick="j.ui.divalog.hide(\''+str+'\');" /></div></div>')
            params = (typeof params === 'undefined')?{}:params;
        $(document.body).append(wrp);
        $('#dlg_'+str+' div.txt').load(url,params,function(){ j[str].init(wrp); });

        return wrp;
    },
    hide:function(str){
        $('#dlg_'+str).remove();
    }
};
