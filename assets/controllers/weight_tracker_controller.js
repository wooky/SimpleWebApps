import { Controller } from "@hotwired/stimulus";
import { visit } from "@hotwired/turbo";
import { Chart } from "chart.js/auto";
import "chartjs-adapter-moment";
import moment from "moment-timezone";
import StreamController from "./stream_controller";

export default class extends Controller {
  static targets = ['chart'];
  static values = {
    'pointClickPath': String,
  };
  static outlets = ['stream'];

  initialize() {
    /** @type { HTMLCanvasElement } */ this.chartTarget;
    /** @type { String } */ this.pointClickPathValue;
  }

  connect() {
    moment.tz.setDefault("UTC");
    this.chart = new Chart(this.chartTarget, {
      type: 'line',
      data: {},
      options: {
        parsing: false,
        scales: {
          x: {
            type: "time",
            time: {
              unit: "day",
              tooltipFormat: "MMM D YYYY",
            },
          },
          y: {
            min: 0,
          },
        },
        onClick: (e) => this.click(e),
      },
    });
  }

  disconnect() {
    if (this.eventSource) {
      this.eventSource.close();
      this.eventSource.removeEventListener('weight_tracker', e => this.dataUpdated(e), false);
      this.eventSource = undefined;
    }
  }

  /**
   * @param {StreamController} outlet 
   */
  streamOutletConnected(outlet) {
    outlet.listenForTopic('weight_tracker', e => this.dataUpdated(e));
  }

  /**
   * @param {Event} e 
   */
  click(e) {
    const elementsClicked = this.chart.getElementsAtEventForMode(e, 'point', {'intersect': true}, true);
    if (elementsClicked.length) {
      const elementClicked = elementsClicked[0];
      const id = this.chart.data.datasets[elementClicked.datasetIndex].data[elementClicked.index].id;
      const url = this.pointClickPathValue.replace(encodeURIComponent("{{id}}"), id);
      visit(url, {frame: "app-modal"});
    }
  }

  /**
   * @param {MessageEvent} e
   */
  dataUpdated(e) {
    /**
     * @type {{
     *     command: string,
     *     id: ?string,
     *     data: ?{
     *         owner: ?string,
     *     },
     * }}
     */
    const payload = JSON.parse(e.data);
    switch (payload.command) {
      case 'initial-data': {
        if (!this.gotInitialData) {
          this.chart.data.datasets = payload.data;
          this.gotInitialData = true;
        } else {
          console.log('Ignoring initial data as chart is already populated');
        }
        break;
      }
      case 'weight-record-created': {
        const idx = this.chart.data.datasets.findIndex(ds => ds.id === payload.data.owner);
        if (idx === -1) {
          console.warn('Cannot find user', payload);
          break;
        }
        this.chart.data.datasets[idx].data.push(payload.data);
        this.chart.data.datasets[idx].data.sort((a, b) => a.x - b.x);
        break;
      }
      case 'weight-record-updated': {
        const setIdx = this.chart.data.datasets.findIndex(ds => ds.id === payload.data.owner);
        if (setIdx === -1) {
          console.warn('Cannot find user', payload);
          break;
        }
        const dataIdx = this.chart.data.datasets[setIdx].data.findIndex(d => d.id === payload.data.id);
        if (dataIdx === -1) {
          console.warn('Cannot find existing data point', payload);
          break;
        }
        this.chart.data.datasets[setIdx].data[dataIdx] = payload.data;
        this.chart.data.datasets[setIdx].data.sort((a, b) => a.x - b.x);
        break;
      }
      case 'weight-record-deleted': {
        const idxs = this.chart.data.datasets.map((ds, i) => [i, ds.data.findIndex(d => d.id === payload.id)]).find(([setIdx, dataIdx]) => dataIdx !== -1);
        if (idxs === undefined) {
          console.warn('Cannot find existing data point');
          break;
        }
        const [setIdx, dataIdx] = idxs;
        this.chart.data.datasets[setIdx].data.splice(dataIdx, 1);
        break;
      }
      case 'relationship-updated':
      case 'relationship-deleted':
        // TODO
        break;
      default:
        console.warn('Unhandled command', payload);
    }
    
    this.chart.update();
  }
}
