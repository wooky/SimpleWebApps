import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    document.addEventListener('click', () => this._closeMenu());
  }

  disconnect() {
    document.removeEventListener('click', () => this._closeMenu());
  }

  toggleMenu(event) {
    event.stopPropagation();
    this.element.classList.toggle('is-active');
  }

  _closeMenu() {
    this.element.classList.remove('is-active');
  }
}
