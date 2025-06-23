<template>
  <div class="pie-chart-container">
    <h3>{{ studentName }} - Mark Distribution</h3>
    <Pie :data="chartData" :options="chartOptions" />
  </div>
</template>

<script>
import { Pie } from 'vue-chartjs'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement
} from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, ArcElement)

export default {
  name: 'StudentPieChart',
  components: { Pie },
  props: {
    studentName: String,
    components: Array,   // e.g., ['quiz', 'assignment', 'Final Exam']
    marks: Object        // e.g., { quiz: 8, assignment: 10, 'Final Exam': 20 }
  },
  computed: {
    chartData() {
      return {
        labels: this.components,
        datasets: [
          {
            label: 'Mark Distribution',
            data: this.components.map(comp => this.marks[comp] ?? 0),
            backgroundColor: [
              '#36A2EB', '#FFCE56', '#FF6384', '#4BC0C0', '#9966FF'
            ]
          }
        ]
      }
    },
    chartOptions() {
      return {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom'
          },
          title: {
            display: true,
            text: 'Student Mark Breakdown (Pie)'
          }
        }
      }
    }
  }
}
</script>

<style scoped>
.pie-chart-container {
  max-width: 400px;
  margin: auto;
  margin-top: 30px;
}
</style>
