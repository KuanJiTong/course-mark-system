<template>
  <DefaultLayout>
    <template #default>
      <div>
        <h2>Performance Trend Graphs</h2>
        
        <!-- Trend Chart: Students' performance over time (Example: Scores in assignments, quizzes, midterms, final exam) -->
        <div>
          <LineChart :data="chartData" :options="chartOptions" />
        </div>
      </div>
    </template>
  </DefaultLayout>
</template>

<script>
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale } from 'chart.js';
import DefaultLayout from '../components/DefaultLayout.vue'; 

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale);

export default {
  components: {
    DefaultLayout,
    LineChart: Line,  // Register the Line chart component from vue-chartjs
  },
  data() {
    return {
      userID: null,
      // Sample data for the performance trend graph
      chartData: {
        labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5'],  // X-axis: Weeks
        datasets: [
          {
            label: 'Assignments',
            data: [80, 85, 88, 92, 90],  // Y-axis: Performance over weeks
            fill: false,
            borderColor: 'rgba(75, 192, 192, 1)',
            tension: 0.1
          },
          {
            label: 'Quizzes',
            data: [75, 78, 80, 82, 85],
            fill: false,
            borderColor: 'rgba(153, 102, 255, 1)',
            tension: 0.1
          },
          {
            label: 'Midterm',
            data: [70, 72, 74, 78, 76],
            fill: false,
            borderColor: 'rgba(255, 159, 64, 1)',
            tension: 0.1
          },
          {
            label: 'Final Exam',
            data: [90, 92, 93, 95, 94],
            fill: false,
            borderColor: 'rgba(255, 99, 132, 1)',
            tension: 0.1
          }
        ]
      },
      chartOptions: {
        responsive: true,
        plugins: {
          title: {
            display: true,
            text: 'Performance Trend Over Time',
          },
          tooltip: {
            mode: 'index',
            intersect: false,
          },
        },
        scales: {
          x: {
            title: {
              display: true,
              text: 'Weeks',
            }
          },
          y: {
            title: {
              display: true,
              text: 'Scores (%)',
            },
            min: 0,
            max: 100,
            ticks: {
              stepSize: 10,
            }
          }
        }
      }
    };
  },
  mounted() {
    // Check authentication
    const user = JSON.parse(sessionStorage.getItem('user'));
    if (!user || !user.user_id) {
      this.$router.push('/login?message=Please login to access performance trends');
      return;
    }
    
    this.userID = user.user_id;
    console.log('Authenticated user ID for performance trends:', this.userID);
  }
};
</script>

<style scoped>
/* Add your custom styles */
h2 {
  text-align: center;
  margin-bottom: 20px;
}

div {
  margin: 20px;
}
</style>
