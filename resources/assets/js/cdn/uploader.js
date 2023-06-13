
import _ from 'lodash';
import axios from 'axios';
import jQuery from 'jquery';
import qq from 'fine-uploader/lib/all';

class FileUploader {
    constructor(element, options) {
        this.collectionName = options.collectionName || 'files';
        this.signEndpoint = options.signEndpoint || '/jpanel/media/cdn/sign';
        this.doneEndpoint = options.doneEndpoint || '/jpanel/media/cdn/done';

        this.previewParent = jQuery(options.previewParent);
        this.previewTemplate = options.previewTemplate;

        this.callbacks = {
            onDragEnter: options.onDragEnter,
            onDragLeave: options.onDragLeave,
            onDrop: options.onDrop,
            onSubmit: options.onSubmit,
            onUpload: options.onUpload,
            onProgress: options.onProgress,
            onComplete: options.onComplete,
            onError: options.onError,
        };

        this.api = new qq.FineUploaderBasic({
            button: element,
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
                onSubmit: this.onSubmit.bind(this),
                onComplete: this.onComplete.bind(this),
                onUpload: this.onUpload.bind(this),
                onProgress: this.onProgress.bind(this),
                onStatusChange: this.onStatusChange.bind(this),
                onError: this.onError.bind(this),
            }
        });

        if (options.dropzone) {
            let dropZoneElements = Array.isArray(options.dropzone) && options.dropzone.length
                ? options.dropzone
                : [element];

            this.api.dragAndDrop = new qq.DragAndDrop({
                dropZoneElements,
                classes: {
                    dropActive: 'dz-drag-hover'
                },
                callbacks: {
                    dragEnter: this.onDragEnter.bind(this),
                    dragLeave: this.onDragLeave.bind(this),
                    processingDroppedFilesComplete: this.onDrop.bind(this)
                }
            });
        }
    }

    getUploadData(id) {
        return this.api._uploadData.retrieve({ id });
    }

    onDragEnter() {
        this.callbacks.onDragEnter && this.callbacks.onDragEnter();
    }

    onDragLeave() {
        this.callbacks.onDragLeave && this.callbacks.onDragLeave();
    }

    onDrop(files) {
        this.api.addFiles(files);
        this.callbacks.onDrop && this.callbacks.onDrop(files);
    }

    onSubmit(id, name) {
        const upload = this.getUploadData(id);

        const payload = {
            filename: name,
            collection_name: this.collectionName,
            content_type: upload.file.type,
        };

        if (this.previewTemplate) {
            upload.previewElement = jQuery(this.previewTemplate);
            this.previewParent.prepend(upload.previewElement);
        }

        return axios.post(this.signEndpoint, payload).then(res => {
            upload.filename = res.data.filename;
            this.api.setEndpoint(res.data.signed_upload_url, id);
            this.callbacks.onSubmit && this.callbacks.onSubmit(upload);
        });
    }

    onUpload(id) {
        const upload = this.getUploadData(id);
        this.callbacks.onUpload && this.callbacks.onUpload(upload);
    }

    onProgress(id, name, uploadedBytes, totalBytes) {
        const upload = this.getUploadData(id);

        if (!upload.previewElement) {
            return;
        }

        const onProgressRun = {
            ticks: 0,
            speed: 0,
            averageSpeed: 0,
            uploadedBytes,
            percentUploaded: _.round(uploadedBytes / totalBytes * 100, 3),
            remaining: '',
            timestamp: (new Date()).getTime(),
        };

        upload.previewElement.find('[data-dz-uploadprogress]').css('width', onProgressRun.percentUploaded + '%');

        if (upload.onProgressRun) {
            onProgressRun.remaining = upload.onProgressRun.remaining;
            onProgressRun.ticks = upload.onProgressRun.ticks + 1;

            const sample = {
                uploadedBytes: onProgressRun.uploadedBytes - upload.onProgressRun.uploadedBytes,
                transferTime: _.round((onProgressRun.timestamp - upload.onProgressRun.timestamp) / 1000, 2)
            };

            onProgressRun.speed = Math.floor(sample.uploadedBytes / sample.transferTime);
            onProgressRun.averageSpeed = Math.floor(0.05 * upload.onProgressRun.speed + (1 - 0.05) * upload.onProgressRun.averageSpeed);

            if (onProgressRun.ticks > 10) {
                let remaining = (totalBytes - uploadedBytes) / onProgressRun.averageSpeed;

                if (remaining < 1 || !Number.isFinite(remaining)) {
                    onProgressRun.remaining = '';
                } else {
                    onProgressRun.remaining = remaining.formatTime();
                }

                upload.previewElement.find('[data-dz-timeremaining]').html(remaining);
            }
        }

        upload.onProgressRun = onProgressRun;

        this.callbacks.onProgress && this.callbacks.onProgress(upload, onProgressRun);
    }

    onComplete(id, name) {
        const upload = this.getUploadData(id);

        const payload = {
            filename: upload.filename,
            collection_name: this.collectionName,
            content_type: upload.file.type,
            size: upload.file.size
        };

        if (upload.status === qq.status.UPLOAD_SUCCESSFUL) {
            return axios.post(this.doneEndpoint, payload)
                .then(res => {
                    this.callbacks.onComplete && this.callbacks.onComplete(upload, res.data);
                });
        }

        this.onError(id, name, upload.status);
    }

    onStatusChange(id, oldStatus, newStatus) {
        if (newStatus === qq.status.REJECTED) {
            this.onError(id, null, newStatus, null);
        }
    }

    onError(id, name, errorReason) {
        const upload = this.getUploadData(id);
        this.callbacks.onError && this.callbacks.onError(upload, errorReason);
    }
}


export default FileUploader;
