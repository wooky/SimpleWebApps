import { Controller } from "@hotwired/stimulus";
import { visit, TurboBeforeStreamRenderEvent } from "@hotwired/turbo";
import { Chart } from "chart.js/auto";
import "chartjs-adapter-moment";
import moment from "moment-timezone";

export default class extends Controller {
  static targets = ['chart'];
  static values = {
    'eventSourceUrl': String,
    'points': Array,
    'pointClickPath': String,
  };

  connect() {
    moment.tz.setDefault("UTC");
    const points = this.pointsValue;
    for (let i = 0; i < points.length; i++) {
      if (!points[i].__self) {
        points[i].hidden = true;
      }
    }
    this.chart = new Chart(this.chartTarget, {
      type: 'line',
      data: {
        datasets: points,
      },
      options: {
        parsing: false,
        scales: {
          x: {
            type: "time",
            time: {
              unit: "day",
              tooltipFormat: "MMM D",
            },
          },
          y: {
            min: 0,
          },
        },
        onClick: (e) => this.click(e),
      },
    });

    this.eventSource = new EventSource(this.eventSourceUrlValue, {withCredentials: true});
    this.eventSource.onmessage = (e) => this.dataUpdated(e);
    // addEventListener("turbo:before-stream-render", (e) => this.turboStream(e));
  }

  disconnect() {
    this.eventSource.close();
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
    const data = JSON.parse(e.data);
    this.chart.data.datasets = data;
    this.chart.update();
  }
}
