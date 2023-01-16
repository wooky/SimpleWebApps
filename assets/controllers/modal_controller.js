import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
  connect() {
    this.element.querySelectorAll('.modal-background, .modal-close, .modal-card-head .delete').forEach(($close) => {
      $close.addEventListener('click', () => {
        this.closeModal();
      });
    });
  }

  closeModal() {
    this.element.classList.remove("is-active");
  }
}
