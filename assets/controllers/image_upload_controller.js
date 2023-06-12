import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ['dropzone', 'dropzoneRow'];

  connect() {
    this.dropzoneTarget.parentNode.addEventListener('dropzone:change', this._onDropzoneChange);
  }

  disconnect() {
    this.dropzoneTarget.parentNode.removeEventListener('dropzone:change', this._onDropzoneChange);
  }

  _onDropzoneChange(event) {
    console.log(event);
  }
}
