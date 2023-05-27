import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  static targets = ['display'];

  connect() {
    this.reset();
  }

  reset() {
    this.displayTarget.innerText = '0';
  }

  digit({ params: { digit } }) {
    if (this.displayTarget.innerText === '0') {
      this.displayTarget.innerText = digit;
    }
    else {
      this.displayTarget.innerText += digit;
    }
  }
}
