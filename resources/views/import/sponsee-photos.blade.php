
@extends('layouts.app')
@section('title', 'Sponsee Photo Importer')

@section('content')

<div class="row">
    <div class="col-lg-12">
        <h1 class="page-header clearfix">
            Sponsee Photo Importer
        </h1>
    </div>
</div>

<div class="row"><div class="col-md-12">

<div id="sponsee-photo-importer-app" class="row" v-cloak>
    <div class="col-md-12 col-lg-10 col-lg-offset-1">

        <div class="panel panel-default">
            <div class="panel-body" style="padding-bottom:0!important">
                <div class="row">
                    <div class="col-sm-6 col-lg-4">
                        <div class="panel-sub-title">Step 1: Select photos</div>
                        <div class="panel-sub-desc">
                            <p>
                                Select all of the photos you'd like to upload. All of the photos should be named
                                with using the sponsee's reference number as the filename.
                            </p>
                            <p class="mt-2">For example: <mark>010-1515.jpg</mark></p>
                            <p class="mt-4">
                                <div class="form-group">
                                    <label class="text-gray-800 mt-[5px]">
                                        Skip sponsees that already have photos
                                    </label>&nbsp;&nbsp;&nbsp;
                                    <toggle-button v-model="skipSponseeWithPhoto"></toggle-button>
                                </div>
                            </p>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-5 col-md-offset-1 col-lg-6 col-lg-offset-2">
                        <form v-if="isPreparing" ref="dropzone" class="dropzone flex items-center m-0 mt-5 ml-auto max-w-md">
                            <div class="dz-message">
                                <div>Drop photos here</div>
                                <small>or</small>
                                <div><button type="button">Select photos</button></div>
                            </div>
                        </form>
                        <div v-else class="dropzone flex items-center m-0 ml-auto max-w-md opacity-50">
                            <div class="dz-message">
                                <div>Drop photos here</div>
                                <small>or</small>
                                <div><button type="button">Select photos</button></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="uploads.length" class="panel panel-default">
            <div class="panel-body">
                <div class="panel-sub-title">Step 2: Review matches</div>
                <div class="panel-sub-desc mb-6">
                    <p>
                        Review the sponsee matches for the photos. Take note of any photos that don't have a sponsee match.
                        You'll likely want to follow up with the customer, the photos may be named incorrectly or the sponsee
                        hasn't been imported yet. When you're ready to proceed click the <strong>Import photos</strong> button below.
                    </p>
                </div>

                <div class="flex flex-wrap">
                    <div v-for="upload in uploads" class="flex items-center mb-3 w-56" :class="{ 'opacity-50': skipSponseeWithPhoto && upload.has_photo }">
                        <div class="shrink-0 mr-2">
                            <svg v-if="skipSponseeWithPhoto && upload.has_photo" class="h-5 w-5 text-yellow-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                <path d="M256 8C119.034 8 8 119.033 8 256s111.034 248 248 248 248-111.034 248-248S392.967 8 256 8zm130.108 117.892c65.448 65.448 70 165.481 20.677 235.637L150.47 105.216c70.204-49.356 170.226-44.735 235.638 20.676zM125.892 386.108c-65.448-65.448-70-165.481-20.677-235.637L361.53 406.784c-70.203 49.356-170.226 44.736-235.638-20.676z" />
                            </svg>
                            <svg v-else-if="upload.sponsee" class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                <path d="M504 256c0 136.967-111.033 248-248 248S8 392.967 8 256 119.033 8 256 8s248 111.033 248 248zM227.314 387.314l184-184c6.248-6.248 6.248-16.379 0-22.627l-22.627-22.627c-6.248-6.249-16.379-6.249-22.628 0L216 308.118l-70.059-70.059c-6.248-6.248-16.379-6.248-22.628 0l-22.627 22.627c-6.248 6.248-6.248 16.379 0 22.627l104 104c6.249 6.249 16.379 6.249 22.628.001z" />
                            </svg>
                            <svg v-else class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                <path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm121.6 313.1c4.7 4.7 4.7 12.3 0 17L338 377.6c-4.7 4.7-12.3 4.7-17 0L256 312l-65.1 65.6c-4.7 4.7-12.3 4.7-17 0L134.4 338c-4.7-4.7-4.7-12.3 0-17l65.6-65-65.6-65.1c-4.7-4.7-4.7-12.3 0-17l39.6-39.6c4.7-4.7 12.3-4.7 17 0l65 65.7 65.1-65.6c4.7-4.7 12.3-4.7 17 0l39.6 39.6c4.7 4.7 4.7 12.3 0 17L312 256l65.6 65.1z" />
                            </svg>
                        </div>
                        <div v-if="upload.background_url" class="flex h-9 w-9 rounded-md bg-cover bg-center overflow-hidden mr-2" :style="{ backgroundImage: upload.background_url }"></div>
                        <div class="leading-none">
                            <div v-if="upload.sponsee" :class="{ 'text-yellow-600': skipSponseeWithPhoto && upload.has_photo }">
                                <div class="font-medium">${ upload.sponsee.name }</div>
                                <small v-if="skipSponseeWithPhoto && upload.has_photo" class="font-normal">Already has photo</small>
                                <small v-else class="font-normal text-muted">${ upload.name }</small>
                            </div>
                            <div v-else class="text-red-800">
                                <div class="font-normal">${ upload.name }</div>
                                <small class="font-normal">No matching sponsee</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="uploads.length" class="panel panel-default">
            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-6 col-lg-4">
                        <div class="panel-sub-title">Step 3: Importing photos</div>
                        <div class="panel-sub-desc">
                            <p>
                                If you're importing a lot of photos it will likely take serveral minutes to complete
                                the import process. <strong>Please <span class="underline">DO NOT</span> close your
                                browser tab or navigate away from this page.</strong>
                            </p>
                        </div>
                        <div v-if="isPreparing" class="flex items-center mt-3">
                            <button type="button" @click="runImport()" :disabled="notReadyToImport" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gcb-700 bg-gcb-200 focus:outline-none focus:border-gcb-300 focus:ring focus:ring-gcb-200 disabled:opacity-50 transition ease-in-out duration-150 font-bold">
                                Import photos
                            </button>
                        </div>
                        <div v-if="hasUploadErrors" class="my-5">
                          <div v-for="upload in uploadErrors" :key="upload.key" class="flex items-center mb-3">
                                <div class="shrink-0 mr-2">
                                    <svg class="h-5 w-5 text-red-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                                        <path d="M256 8C119 8 8 119 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm121.6 313.1c4.7 4.7 4.7 12.3 0 17L338 377.6c-4.7 4.7-12.3 4.7-17 0L256 312l-65.1 65.6c-4.7 4.7-12.3 4.7-17 0L134.4 338c-4.7-4.7-4.7-12.3 0-17l65.6-65-65.6-65.1c-4.7-4.7-4.7-12.3 0-17l39.6-39.6c4.7-4.7 12.3-4.7 17 0l65 65.7 65.1-65.6c4.7-4.7 12.3-4.7 17 0l39.6 39.6c4.7 4.7 4.7 12.3 0 17L312 256l65.6 65.1z" />
                                    </svg>
                                </div>
                                <div class="leading-none">
                                    <div class="font-medium">${ upload.sponsee.name } <small class="font-normal text-muted">${ upload.name }</small></div>
                                    <small class="font-normal text-red-800">${ upload.error_reason }</small>
                                </div>
                            </div>
                        </div>
                        <div v-if="isRunning" class="shadow w-full bg-gray-100 rounded-md overflow-hidden mt-3">
                            <div class="bg-gcp-500 text-xs leading-none py-1 text-center text-white font-bold transition-all duration-500 ease-in-out" :style="{ width: percentUploaded + '%' }"></div>
                        </div>
                        <div v-else-if="isComplete" class="text-lg font-bold text-green-400 mt-3">
                            Uploads completed.
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-5 col-md-offset-1 col-lg-6 col-lg-offset-2">
                        <div v-if="isRunning && isUploading" class="flex items-center mr-8 pt-6">
                            <div v-if="uploading.background_url" class="flex h-20 w-20 rounded-md bg-cover bg-center overflow-hidden mr-3" :style="{ backgroundImage: uploading.background_url }"></div>
                            <div class="flex-grow">
                                <div class="text-lg">${ uploading.sponsee.name }</div>
                                <small class="font-normal text-muted">${ uploading.name }</small>
                                <div class="shadow w-full bg-gray-100 rounded-md overflow-hidden mt-2 max-w-md">
                                    <div ref="uploadBar" class="bg-gcp-500 text-xs leading-none py-0.5 text-center text-white font-bold transition-all duration-500 ease-in-out" :style="{ width: uploading.percent_uploaded + '%' }"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection
