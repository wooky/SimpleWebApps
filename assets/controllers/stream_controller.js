import { Controller } from "@hotwired/stimulus";
import { connectStreamSource, disconnectStreamSource } from "@hotwired/turbo";

export default class extends Controller {
  static values = {
    'eventSourceUrl': String,
  };

  connect() {
    if (this.disconnectTask) {
      clearTimeout(this.disconnectTask);
      this.disconnectTask = undefined;
    }

    const topics = document.querySelector('meta[name="simplewebapps:topics"]').content;
    if (this.topics !== topics) {
      this._closeConnection();
      this.topics = topics;
      const url = this.eventSourceUrlValue.replace(encodeURIComponent("{{topics}}"), topics);
      this.es = new EventSource(url);
      connectStreamSource(this.es);
      if (this.unprocessedEvents) {
        this.unprocessedEvents.forEach(ec => this._listenForEventsNow(ec));
        this.unprocessedEvents = undefined;
      }
    }
  }

  disconnect() {
    this.disconnectTask = setTimeout(() => this._closeConnection(), 5000);
  }

  _closeConnection() {
    if (this.es) {
      this.es.close();
      disconnectStreamSource(this.es);
      this.es = undefined;
    }
  }

  listenForEvents(event, callback) {
    if (this.es) {
      this._listenForEventsNow([event, callback]);
    } else {
      if (!this.unprocessedEvents) {
        this.unprocessedEvents = [];
      }
      this.unprocessedEvents.push([event, callback]);
    }
  }

  _listenForEventsNow([event, callback]) {
    this.es.addEventListener(event, callback, false);
  }
}
