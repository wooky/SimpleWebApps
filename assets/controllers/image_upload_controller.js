import { Controller } from "@hotwired/stimulus";
import Cropper from "cropperjs";

export default class extends Controller {
  static targets = ['stepMarker', 'stepStage', 'dropzone', 'cropper', 'notAnImage'];

  connect() {
    this._reset();
    this.dropzoneTarget.parentNode.addEventListener('dropzone:change', e => this._onDropzoneChange(e));
    this.dropzoneTarget.parentNode.addEventListener('dropzone:clear', e => this._reset(e));
  }

  disconnect() {
    this.dropzoneTarget.parentNode.removeEventListener('dropzone:change', e => this._onDropzoneChange(e));
    this.dropzoneTarget.parentNode.removeEventListener('dropzone:clear', e => this._reset(e));
  }

  _reset() {
    this._setStep(0);

    [this.notAnImageTarget]
      .forEach(el => el.classList.add('is-hidden'));

    if (this.cropper) {
      this.cropper.destroy();
    }
  }

  _setStep(step) {
    this.stepMarkerTargets.forEach((el, i) =>
      (step === i) ? el.classList.add('is-active') : el.classList.remove('is-active')
    );
    this.stepStageTargets.forEach((el, i) =>
      (step === i) ? el.classList.remove('is-hidden') : el.classList.add('is-hidden')
    );
  }

  _onDropzoneChange(event) {
    /** @type {File} */ const file = event.detail;
    if (!file.type || file.type.indexOf('image') === -1 || typeof FileReader === 'undefined') {
      this.notAnImageTarget.classList.remove('is-hidden');
      return;
    }

    const reader = new FileReader();
    reader.addEventListener('load', (event) => {
      this.cropperTarget.src = event.target.result;
      this.cropperTarget.classList.remove('is-hidden');
      this.cropper = new Cropper(this.cropperTarget, {
        viewMode: 2,
        modal: false,
      });
      this._setStep(1);
    });
    reader.readAsDataURL(file);
  }
}
