<h1 class="page-header">
    Check-In Look-Up
</h1>

<style>
    #checkin-app .thumbnail {}
        #checkin-app .highlighted { background-color:#faebcc; text-shadow:none; color:#000; }
        #checkin-app .thumbnail .headline-overlay { background-size: cover; background-position: center center; padding:20px 10px; color:#fff; font-weight:bold; text-shadow:2px 2px #000; text-align:center; margin:-4px -4px 0px -4px; border-radius:4px 4px 0px 0px; }
        #checkin-app .thumbnail .caption { padding-bottom:0px; }
        #checkin-app .thumbnail hr { margin:10px -13px; }
    #check-in-search { margin-bottom:40px; }

</style>

<div id="checkin-app" v-cloak>
    <input type="search" id="check-in-search" name="keywords" v-model.lazy="keywords" value="" placeholder="Search Name, Email or Phone..." class="text-center input-lg form-control">

    <div v-if="status == 'ready' && items.length > 0" class="row">
        <div v-for="item in items" class="col-sm-6 col-md-4 col-lg-3">
            <div class="thumbnail">
                <div class="headline-overlay" :style="{background: 'linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url(' + item.image_thumb + ')'}">
                    <h3>${ item.description }</h3>

                </div>

                <div class="caption">

                    <div class="">
                        <div style="margin-bottom:3px;">
                            <div class="text-bold pull-right">$${ formatPrice(item.total) }</div>
                            <div class="text-bold">${ item.order.invoicenumber }</div>
                        </div>
                        <div style="margin-bottom:3px;" class="text-sm text-muted">
                            <div class="pull-right"><i :class="'fa '+item.order.fa_icon"></i> ${ item.order.billingcardlastfour }</div>
                            <div class="">${ item.order.source_and_date_string }</div>
                        </div>
                        <div class="">
                            <div><span v-html="highlight(item.order.billing_first_name)"></span> <span v-html="highlight(item.order.billing_last_name)"></span></div>
                            <div v-show="item.order.billingemail" v-html="highlight(item.order.billingemail)"></div>
                            <div v-show="item.order.billingphone">${ item.order.billingphone }</div>
                        </div>
                    </div>

                    <hr>

                    <div v-if="item.fields.length > 0" class="bottom-gutter-sm">
                        <div v-for="field in item.fields">
                            ${ field.name }: <span v-html="highlight(field.pivot.value)"></span>
                        </div>
                    </div>

                    <p>
                        <a target="_blank" :href="'<?= e(route('backend.orders.checkin')) ?>?o='+item.order.id+'&i='+item.id" v-if="item.checkins.length == 0" class="btn btn-primary btn-sm" role="button"><i class="fa fa-square-o"></i> Check-In</a>
                        <a target="_blank" :href="'<?= e(route('backend.orders.checkin')) ?>?o='+item.order.id+'&i='+item.id" v-if="item.checkins.length > 0" class="btn btn-primary btn-outline btn-sm" role="button"><i class="fa fa-check-square-o"></i> Checked-In &nbsp;<span class="badge">${ item.checkins.length }</span></a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div v-show="status == 'ready' && items.length == 0" class="text-center text-lg text-muted">
        No Results
    </div>

    <div v-show="status == 'loading' && items.length == 0" class="text-center text-lg text-muted">
        <i class="fa fa-spin fa-spinner"></i> Searching...
    </div>
</div>

<script>
spaContentReady(function() {
    var checkinApp = (function(){

        function arrow(context, fn) {
            return fn.bind(context);
        }

        function CheckInComponent(selector) {
            this.selector = selector;

            this.vm = new Vue({
                el: this.selector,
                delimiters: ['${', '}'],
                data: {
                    'items': [],
                    'keywords': null,
                    'status': 'ready'
                },
                watch: {
                    'keywords': function(new_value, old_value) {
                        if (new_value != old_value) {
                            if (new_value == '') {
                                this.status = 'ready';
                                this.items = [];
                            } else {
                                this.search();
                            }
                        }
                    }
                },
                methods: {
                    search: function(event) {
                        this.status = 'loading';
                        this.items = [];
                        axios.get('/jpanel/check-ins/search/'+escape(this.keywords))
                            .then(arrow(this, function(response){
                                this.items = response.data;
                            }))
                            .finally(arrow(this, function(){
                                this.status = 'ready';
                            }));
                            //.catch(function(){  });
                    },
                    highlight(text) {
                        return (text || '').replace(new RegExp(this.keywords, 'gi'), '<span class="highlighted">$&</span>');
                    },
                    formatPrice(value) {
                        let val = (value/1).toFixed(2);
                        return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    }
                }
            });
        }

        if (document.querySelectorAll('#checkin-app').length) {
            return new CheckInComponent('#checkin-app');
        }

    })();
});
</script>
