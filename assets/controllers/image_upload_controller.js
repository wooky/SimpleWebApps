import { Controller } from "@hotwired/stimulus";
import { visit } from "@hotwired/turbo";
import Cropper from "cropperjs";

export default class extends Controller {
  static targets = ['stepMarker', 'stepStage', 'back', 'cropper', 'notAnImage', 'upload', 'image'];
  static values = {
    backUrl: String,
  };

  connect() {
    this._reset();
    this.imageTarget.parentNode.addEventListener('dropzone:change', e => this._onDropzoneChange(e));
  }

  disconnect() {
    this.imageTarget.parentNode.removeEventListener('dropzone:change', e => this._onDropzoneChange(e));
  }

  goBack() {
    (this.step === 0)
      ? visit(this.backUrlValue, {frame: "app-modal"})
      : this._setStep(this.step - 1)
    ;
  }

  uploadCrop() {
    this.cropper.getCroppedCanvas().toBlob((blob) => {
      // https://stackoverflow.com/a/66466544
      const file = new File([blob], 'image.png', {type: 'image/png'});
      const container = new DataTransfer();
      container.items.add(file);
      this.imageTarget.files = container.files;
      this.element.requestSubmit();
      this._setStep(2);
    });
  }

  _reset() {
    this._setStep(0);
  }

  _setStep(step) {
    this.step = step;
    [this.notAnImageTarget, this.uploadTarget]
      .forEach(el => el.classList.add('is-hidden'));

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

      if (this.cropper) {
        this.cropper.destroy();
      }
      this.cropper = new Cropper(this.cropperTarget, {
        viewMode: 2,
        modal: false,
      });

      this.application.getControllerForElementAndIdentifier(this.imageTarget.parentNode, 'symfony--ux-dropzone--dropzone').clear(); // TODO ugly hack
      this._setStep(1);
      this.uploadTarget.classList.remove('is-hidden');
    });
    reader.readAsDataURL(file);
  }
}
