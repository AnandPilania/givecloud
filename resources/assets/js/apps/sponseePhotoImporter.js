
import _ from 'lodash';
import axios from 'axios';
import qq from 'fine-uploader/lib/all';
import Vue from 'vue';

export default function(selector) {
    return new Vue({
        el: selector,
        delimiters: ['${', '}'],
        data: {
            status: 'preparing',
            uploads: [],
            uploader: null,
            activeUpload: null,
            skipSponseeWithPhoto: true,
            numberOfCompletedUploads: 0,
        },
        mounted() {
            this.setupDropzone();
        },
        computed: {
            isComplete() {
                return this.status === 'complete';
            },
            isPreparing() {
                return this.status === 'preparing';
            },
            isRunning() {
                return this.status === 'running';
            },
            isUploading() {
                return Boolean(this.uploading);
            },
            hasUploadErrors() {
                return this.uploadErrors.length > 0;
            },
            notReadyToImport() {
                return this.sponseeUploads.length === 0;
            },
            sponseeUploads() {
                return this.uploads.filter(upload => {
                    return this.skipSponseeWithPhoto && upload.has_photo ? false : Boolean(upload.sponsee);
                });
            },
            uploadErrors() {
                return this.sponseeUploads.filter(upload => {
                    return upload.status === 'error';
                });
            },
            uploading() {
                return this.activeUpload === null ? null : this.uploads[this.activeUpload];
            },
            percentUploaded() {
                return this.sponseeUploads.length
                    ? Math.round(this.numberOfCompletedUploads / this.sponseeUploads.length * 100)
                    : 0;
            }
        },
        methods: {
            runImport() {
                this.status = 'running';
                this.uploader.uploadStoredFiles();
            },
            setupDropzone() {
                this.uploader = new qq.FineUploaderBasic({
                    autoUpload: false,
                    button: this.$refs.dropzone,
                    maxConnections: 1,
                    request: {
                        method: 'PUT',
                        forceMultipart: false,
                        paramsInBody: false,
                        omitDefaultParams: true,
                        requireSuccessJson: false
                    },
                    cors: {
                        expected: true,
                        sendCredentials: false
                    },
                    callbacks: {
                        onSubmitted: this.onUploaderSubmitted.bind(this),
                        onValidateBatch: this.onUploaderValidateBatch.bind(this),
                        onUpload: this.onUploaderUpload.bind(this),
                        onProgress: this.onUploaderProgress.bind(this),
                        onComplete: this.onUploaderComplete.bind(this),
                        onAllComplete: this.onUploaderAllComplete.bind(this),
                        onStatusChange: this.onUploaderStatusChange.bind(this),
                        onError: this.onUploaderError.bind(this),
                    },
                    validation: {
                        acceptFiles: 'image/*',
                        allowedExtensions: ['gif', 'jpg', 'jpeg', 'png'],
                    },
                });
                this.uploader.dragAndDrop = new qq.DragAndDrop({
                    dropZoneElements: [this.$refs.dropzone],
                    classes: {
                        dropActive: 'dz-drag-hover'
                    },
                    callbacks: {
                        processingDroppedFilesComplete: this.onUploaderFilesDropped.bind(this)
                    }
                });
            },
            getUploadData(id) {
                return this.uploader._uploadData.retrieve({ id });
            },
            getUploadsIndex(id) {
                return this.getUploadData(id).idx;
            },
            onUploaderFilesDropped(files) {
                this.uploader.addFiles(files);
            },
            onUploaderStatusChange(id, oldStatus, newStatus) {
                if (newStatus === qq.status.REJECTED) {
                    this.onUploaderError(id, null, newStatus, null);
                }
            },
            onUploaderSubmitted(id) {
                const data = this.getUploadData(id);
                data.idx = _.findIndex(this.uploads, { name: data.name });
                this.uploads[data.idx].id = id;
                this.uploads[data.idx].submitted = true;
                this.uploads[data.idx].background_url = `url(${URL.createObjectURL(data.file)})`;
                if (this.uploads[data.idx].status === 'error' || (this.skipSponseeWithPhoto && this.uploads[data.idx].has_photo)) {
                    this.uploader.cancel(id);
                }
            },
            onUploaderValidateBatch(fileOrBlobDataArray) {
                const files = fileOrBlobDataArray.map(data => data.name);
                return axios.post('/jpanel/import/sponsee-photos/check', { files }).then(res => {
                    res.data.forEach(file => {
                        const upload = {
                            id: null,
                            idx: this.uploads.length,
                            key: _.uniqueId('sponseePhotos'),
                            status: 'queued',
                            submitted: false,
                            name: file.name,
                            filename: null,
                            background_url: null,
                            uploaded_bytes: 0,
                            percent_uploaded: 0,
                            error_reason: null,
                            sponsee: file.sponsee,
                            has_photo: !!file.sponsee?.has_photo,
                        };
                        if (! upload.sponsee) {
                            upload.status = 'error';
                            upload.error_reason = 'No matching sponsee';
                        }
                        this.uploads.push(upload);
                    });
                });
            },
            async onUploaderUpload(id, name) {
                const data = this.getUploadData(id);
                const payload = {
                    filename: name,
                    content_type: data.file.type,
                };
                this.activeUpload = data.idx;
                this.uploads[data.idx].status = 'uploading';
                try {
                    const res = await axios.post('/jpanel/import/sponsee-photos/sign', payload);
                    this.uploads[data.idx].filename = res.data.filename;
                    this.uploader.setEndpoint(res.data.signed_upload_url, id);
                    return res;
                } catch (err) {
                    if (err?.response?.data?.error) {
                        this.onUploaderError(id, name, err.response.data.error);
                    }
                    throw err;
                }
            },
            onUploaderProgress(id, name, uploadedBytes, totalBytes) {
                const idx = this.getUploadsIndex(id);
                this.uploads[idx].uploaded_bytes = uploadedBytes;
                this.uploads[idx].percent_uploaded = _.round(uploadedBytes / totalBytes * 100, 3);
            },
            async onUploaderComplete(id, name) {
                const data = this.getUploadData(id);
                this.uploads[data.idx].percent_uploaded = 100;
                this.numberOfCompletedUploads++;
                if (data.status === qq.status.UPLOAD_SUCCESSFUL) {
                    const payload = {
                        sponsee: this.uploads[data.idx].sponsee.id,
                        filename: this.uploads[data.idx].filename,
                        content_type: data.file.type,
                        size: data.file.size
                    };
                    try {
                        const res = await axios.post('/jpanel/import/sponsee-photos/attach', payload)
                        this.uploads[data.idx].status = 'complete';
                        return res;
                    } catch (err) {
                        if (err?.response?.data?.error) {
                            this.onUploaderError(id, name, err.response.data.error);
                        }
                        throw err;
                    }
                }
                this.onUploaderError(id, name, data.status);
            },
            onUploaderError(id, name, errorReason) {
                const idx = this.getUploadsIndex(id);
                this.uploads[idx].status = 'error';
                if (!this.uploads[idx].error_reason) {
                    this.uploads[idx].error_reason = errorReason || 'Unknown error';
                }
            },
            onUploaderAllComplete() {
                this.status = 'complete';
                this.activeUpload = null;
            },
        }
    });
}
