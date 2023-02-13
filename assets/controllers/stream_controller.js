import { Controller } from "@hotwired/stimulus";
import { connectStreamSource, disconnectStreamSource } from "@hotwired/turbo";

export default class StreamController extends Controller {
  static values = {
    'eventSourceUrl': String,
  };

  initialize() {
    /** @type { String } */ this.eventSourceUrlValue;
    document.documentElement.addEventListener('turbo:load', () => this._establishConnection());
    this._establishConnection();
    if (this.initialConnection === undefined) {
      this.initialConnection = true;
    }
  }

  connect() {
    if (this.disconnectTask) {
      clearTimeout(this.disconnectTask);
      this.disconnectTask = undefined;
    }
  }

  disconnect() {
    this.disconnectTask = setTimeout(() => this._closeConnection(), 5000);
  }

  listenForTopic(topics, callback) {
    this.topics = topics;
    this.eventListener = callback;
    if (this.initialConnection) {
      this._establishConnection();
    }
  }

  _establishConnection() {
    const topics = document.querySelector('meta[name="simplewebapps:topics"]').content;
    if (topics !== 'message' || topics !== this.topics) {
      this._closeConnection();
    }
    if (topics) {
      this.topics = topics;
    }
    if (this.topics && !this.es) {
      const url = this.eventSourceUrlValue.replace(encodeURIComponent("{{topics}}"), this.topics);
      this.es = new EventSource(url);
      connectStreamSource(this.es);
      this.initialConnection = false;
    }
    if (this.eventListener) {
      this.es.addEventListener(this.topics, this.eventListener, false);
      this.eventListener = undefined;
    }
  }

  _closeConnection() {
    if (this.es) {
      this.es.close();
      disconnectStreamSource(this.es);
      this.es = undefined;
    }
  }
}
