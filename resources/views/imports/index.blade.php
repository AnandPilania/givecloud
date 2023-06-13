@extends('layouts.app')
@section('title', 'Imports')

@section('content')

<div class="sm:flex sm:items-center">
    <div class="sm:flex-auto">
        <h1 class="text-4xl font-extrabold text-gray-900">Imports</h1>
    </div>
    <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
        <a href="{{ route('backend.imports.create') }}" class="btn btn-success btn-pill"><i class="fa fa-plus"></i> Create Import</a>
    </div>
</div>
@inject('flash', 'flash')
{{ $flash->output() }}

<div class="px-4 sm:px-6 lg:px-8 mb-36">
    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50 divide-y divide-gray-300">
                        <tr class="divide-x divide-gray-200">
                            <th rowspan="2" scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">View</span>
                            </th>
                            <th rowspan="2" scope="col" class="align-bottom	px-3 py-3.5 text-left text-sm font-bold text-gray-900">Type</th>
                            <th rowspan="2" scope="col" class="align-bottom	px-3 py-3.5 text-left text-sm font-bold text-gray-900">Date created</th>
                            <th rowspan="2" scope="col" class="align-bottom	px-3 py-3.5 text-center text-sm font-bold text-gray-900">Status</th>
                            <th colspan="4" scope="col" class="align-bottom	px-3 py-3.5 text-center text-sm font-bold text-gray-900">Records</th>
                        </tr>
                        <tr class="divide-x divide-gray-200">
                            <th scope="col" class="border-l border-gray-200 align-bottom px-3 py-3.5 text-center text-sm font-bold text-gray-900">Total</th>
                            <th scope="col" class="align-bottom	px-3 py-3.5 text-center text-sm font-bold text-gray-900">Added</th>
                            <th scope="col" class="align-bottom	px-3 py-3.5 text-center text-sm font-bold text-gray-900">Updated</th>
                            <th scope="col" class="align-bottom	px-3 py-3.5 text-center text-sm font-bold text-gray-900">Error</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($imports as $import)
                            @php
                                $step = app(\Ds\Services\Imports\ImportService::class)->getStepForStage($import)
                            @endphp

                            <tr class="divide-x divide-gray-200 {{ $loop->even ? 'bg-gray-50' : '' }}">
                                <td class="whitespace-nowrap py-4 pl-3 pr-4 text-center text-sm font-medium sm:pr-6">
                                    <a href="{{ route('backend.imports.show', $import) }}" class="text-brand-blue hover:text-brand-purple">View</a>
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-semibold text-gray-90">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 flex-shrink-0">
                                            <img class="h-10 w-10 rounded-full" src="{{ $import->icon }}" alt="">
                                        </div>
                                        <div class="ml-4">
                                            <div class="font-medium text-gray-900"> {{ $import->friendly_name }}</div>
                                            @if ($import->file)
                                            <a href="{{ route('backend.imports.download', $import) }}" class="text-gray-500 underline hover:text-brand-purple">
                                                {{ $import->file_name }}
                                            </a>
                                            @else
                                                <span class="text-gray-500">No file yet</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <div class="text-gray-900">{{ $import->created_at->format('M j') }}</div>
                                    <div class="text-gray-500">{{ $import->created_at->diffForHumans() }}</div>
                                </td>

                                <td class="whitespace-nowrap px-3 py-4 text-center text-sm text-gray-500">
                                    @if($import->stage === 'done')
                                    <span class="inline-flex rounded-full capitalize bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">
                                        {{ $import->stage }}
                                    </span>
                                    @elseif($import->stage === 'aborted')
                                    <span class="inline-flex rounded-full capitalize px-2 text-xs font-semibold leading-5 bg-pink-100 text-pink-800">
                                        {{ $import->stage }}
                                    </span>
                                    @elseif($import->stage === 'analysis_queue')
                                        <span class="inline-flex rounded-full capitalize px-2 text-xs font-semibold leading-5 bg-yellow-100 text-yellow-800">
                                        {{ str_replace('_', ' ', $import->stage) }}
                                    </span>
                                    @elseif($import->stage === 'import_ready')
                                        <span class="inline-flex rounded-full capitalize px-2 text-xs font-semibold leading-5 bg-green-100 text-green-800">
                                        {{ str_replace('_', ' ', $import->stage) }}
                                    </span>
                                    @elseif($import->stage === 'error')
                                        <span class="inline-flex rounded-full capitalize px-2 text-xs font-semibold leading-5 bg-red-100 text-red-800">
                                        {{ str_replace('_', ' ', $import->stage) }}
                                    </span>
                                    @else
                                    <span class="inline-flex rounded-full capitalize px-2 text-xs font-semibold leading-5 bg-gray-100 text-gray-800">
                                        Draft
                                    </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">{{ (int) $import->total_records }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">{{ (int) $import->added_records }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">{{ (int) $import->updated_records }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 text-center">{{ (int) $import->error_records }}</td>
                            </tr>

                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
