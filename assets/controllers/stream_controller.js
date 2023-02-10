import { Controller } from "@hotwired/stimulus";
import { connectStreamSource, disconnectStreamSource } from "@hotwired/turbo";

export default class StreamController extends Controller {
  static values = {
    'eventSourceUrl': String,
  };

  initialize() {
    /** @type { String } */ this.eventSourceUrlValue;
    this._openConnection('');
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

  listenForTopic(topic, callback) {
    if (this.topic !== topic) {
      this._closeConnection();
      this._openConnection(topic);
      this.es.addEventListener(topic, callback, false);
    }
  }

  _openConnection(topic) {
    this.topic = topic;
    const url = this.eventSourceUrlValue.replace(encodeURIComponent("{{topics}}"), this.topic);
    this.es = new EventSource(url);
    connectStreamSource(this.es);
  }

  _closeConnection() {
    if (this.es) {
      this.es.close();
      disconnectStreamSource(this.es);
      this.es = undefined;
    }
  }
}
