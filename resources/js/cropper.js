import Cropper from 'cropperjs';
import 'cropperjs/dist/cropper.css';

window.Cropper = Cropper;

window.menuImageCropper = function () {
    return {
        cropModal: false,
        cropper: null,
        imageUrl: '',
        previewUrl: '',
        onFileSelect(event) {
            const file = event.target.files[0];
            if (!file) {
                return;
            }

            const reader = new FileReader();
            reader.onload = (e) => {
                this.imageUrl = e.target.result;
                this.cropModal = true;

                this.$nextTick(() => {
                    const img = document.getElementById('crop-image');
                    if (!img) {
                        return;
                    }

                    this.cropper = new Cropper(img, {
                        aspectRatio: 1,
                        viewMode: 1,
                        background: false,
                    });
                });
            };
            reader.readAsDataURL(file);

            event.target.value = '';
        },
        applyCrop() {
            if (!this.cropper) {
                return;
            }

            const canvas = this.cropper.getCroppedCanvas({ width: 800, height: 800 });
            const dataUrl = canvas.toDataURL('image/webp', 0.9);

            this.$wire.set('fotoMenuCropped', dataUrl);
            this.$wire.set('removeFoto', false);
            this.previewUrl = dataUrl;
            this.closeCrop();
        },
        closeCrop() {
            if (this.cropper) {
                this.cropper.destroy();
                this.cropper = null;
            }

            this.cropModal = false;
            this.imageUrl = '';
        },
    };
};
