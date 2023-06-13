<?php

namespace Ds\Http\Controllers;

use Ds\Models\Tax;

class TaxController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth');
        $this->middleware('requires.feature:taxes');
    }

    public function destroy()
    {
        user()->canOrRedirect('tax.edit');

        $tax = Tax::findOrFail(request('id'));
        $tax->deleted_at = now();
        $tax->deleted_by = user('id');
        $tax->save();

        return redirect()->to('jpanel/taxes');
    }

    public function index()
    {
        user()->canOrRedirect('tax');

        $__menu = 'products.taxes';

        pageSetup('Tax Rules', 'jpanel');

        $query = sprintf('SELECT p.*, IFNULL(t1.regioncount,0) AS regioncount FROM `producttax` p LEFT JOIN (SELECT taxid, COUNT(*) AS regioncount FROM producttaxregion GROUP BY taxid) t1 ON t1.taxid = p.id WHERE deleted_at IS NULL ORDER BY code');
        $qList = db_query($query);

        if (! $qList) {
            $qList_len = 0;
        } else {
            $qList_len = db_num_rows($qList);
        } // store the length

        return $this->getView('taxes/index', compact('__menu', 'query', 'qList', 'qList_len'));
    }

    public function save()
    {
        user()->canOrRedirect('tax.edit');

        if (request('id')) {
            $productTax = Tax::findOrFail(request('id'));
        } else {
            $productTax = new Tax;
        }

        $productTax->code = request('code');
        $productTax->description = request('description');
        $productTax->city = request('city');
        $productTax->rate = request('rate');
        $productTax->save();

        // delete previous state/prov links
        $productTax->regions()->detach();

        // add new links
        foreach (request('regionids', []) as $i => $v) {
            $productTax->regions()->attach($v);
        }

        return redirect()->to('jpanel/taxes');
    }

    public function view()
    {
        user()->canOrRedirect('tax');

        $__menu = 'products.taxes';

        if (request('i')) {
            $tax = Tax::findOrFail(request('i'));
            $title = $tax->code;
        } else {
            $tax = new Tax;
            $title = 'Add Tax';
        }

        pageSetup($title, 'jpanel');

        return $this->getView('taxes/view', compact('__menu', 'tax'));
    }
}
