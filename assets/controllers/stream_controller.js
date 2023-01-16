import { Controller } from "@hotwired/stimulus";
import { connectStreamSource, disconnectStreamSource } from "@hotwired/turbo";

export default class extends Controller {
  connect() {
    if (this.disconnectTask) {
      clearTimeout(this.disconnectTask);
    } else {
      this.es = new EventSource(this.element.dataset.mercureUrl, {
        withCredentials: true,
      });
      connectStreamSource(this.es);
    }
  }

  disconnect() {
    this.disconnectTask = setTimeout(() => {
      this.es.close();
      disconnectStreamSource(this.es);
    }, 5000); 
  }
}
