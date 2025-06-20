<template>
  <div class="chart-container">
    <Line :data="chartData" :options="chartOptions" />
  </div>
</template>

<script>
import { Line } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  LineElement,
  CategoryScale,
  LinearScale,
  PointElement
} from 'chart.js'

ChartJS.register(
  Title, Tooltip, Legend, LineElement,
  CategoryScale, LinearScale, PointElement
)

export default {
  name: 'StudentProgressChart',
  components: { Line },
  props: {
    studentName: String,
    components: Array, // e.g., ['quiz', 'assignment', 'test']
    marks: Object      // e.g., { quiz: 5, assignment: 8, test: 7 }
  },
  computed: {
    chartData() {
      return {
        labels: this.components,
        datasets: [
          {
            label: `Marks for ${this.studentName}`,
            data: this.components.map(comp => this.marks[comp] ?? 0),
            fill: false,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.2
          }
        ]
      }
    },
    chartOptions() {
      return {
        responsive: true,
        plugins: {
          legend: { display: true },
          title: {
            display: true,
            text: 'Student Performance Trend'
          }
        }
      }
    }
  }
}
</script>

<style scoped>
.chart-container {
  max-width: 600px;
  margin: auto;
  padding: 20px;
}
</style>
