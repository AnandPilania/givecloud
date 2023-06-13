<?php

namespace Ds\Http\Controllers\Settings;

use Ds\Domain\HotGlue\HotGlue;
use Ds\Domain\Salesforce\Services\SalesforceClientService;
use Ds\Http\Controllers\Controller;
use Exception;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SalesforceSettingsController extends Controller
{
    protected SalesforceClientService $salesforceClientService;

    public function __construct(SalesforceClientService $salesforceClientService)
    {
        $this->salesforceClientService = $salesforceClientService;
        parent::__construct();
    }

    public function index(): View
    {
        $resolved = app(HotGlue::class)->target('salesforce');

        return view('settings.integrations.salesforce', [
            'isConnected' => $resolved->isConnected(),
            'config' => app(HotGlue::class)->config('salesforce'),
        ]);
    }

    public function legacy(): View
    {
        return view('settings.integrations.salesforce-legacy', [
            'loginUrl' => config('database.connections.soql.loginURL'),
            'token' => $this->salesforceClientService->hasToken() ? $this->salesforceClientService->token() : null,
        ]);
    }

    public function store(): RedirectResponse
    {
        sys_set();

        $this->flash->success('Saved successfully');

        return redirect()->back();
    }

    public function connect(): RedirectResponse
    {
        return $this->salesforceClientService->authenticate();
    }

    public function callback(): RedirectResponse
    {
        try {
            $this->salesforceClientService->callback();
            $this->flash->success('Successfully connected to Salesforce');
        } catch (Exception $e) {
            notifyException($e);
            $this->flash->error($this->salesforceClientService->getExceptionMessage($e));
        }

        return redirect()->route('backend.settings.integrations.salesforce.legacy');
    }

    public function test(): RedirectResponse
    {
        try {
            $this->salesforceClientService->test();
            $this->flash->success('Connection to Salesforce tested successfully');
        } catch (Exception $e) {
            notifyException($e);
            $this->flash->error($this->salesforceClientService->getExceptionMessage($e));
        }

        return redirect()->back();
    }

    public function disconnect(): RedirectResponse
    {
        try {
            $this->salesforceClientService->revoke();
            $this->flash->success('Token revoked successfully');
        } catch (RequestException $e) {
            notifyException($e);
            $this->flash->error($this->salesforceClientService->getExceptionMessage($e));
        }

        return redirect()->back();
    }
}
