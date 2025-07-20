<template>
  <div>
    <h2 class="mt-4 mb-4">Compare with Coursemates</h2>
    <div class="form-group">
      <label for="course">Course:</label>
      <select class="form-select" v-model="selectedSectionId" @change="fetchMarks" required>
        <option disabled value="">-- Select Course --</option>
        <option v-for="course in courses" :key="course.sectionId" :value="course.sectionId">
          {{ course.courseCode }}-{{ course.sectionNumber }} {{ course.courseName }}
        </option>
      </select>
    </div>
    <div class="form-group" v-if="components.length">
      <label for="component">Component:</label>
      <select class="form-select" v-model="selectedComponent">
        <option disabled value="">-- Select Component --</option>
        <option value="total">Total Mark</option>
        <option v-for="c in components" :key="c.componentId" :value="c.componentName">{{ c.componentName }}</option>
        <option value="finalExam">Final Exam</option>
      </select>
    </div>
    <div v-if="marks.length" style="margin-bottom: 32px;">
      <Bar :data="barChartData" :options="barChartOptions" />
    </div>
    <table v-if="marks.length">
      <thead>
        <tr>
          <th>Student</th>
          <th v-if="selectedComponent === 'total'">Total Mark</th>
          <th v-else-if="selectedComponent === 'finalExam'">Final Exam</th>
          <th v-else>{{ selectedComponent }}</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(item,index) in marks" :key="item.studentId" :class="{ 'highlight': item.studentId == studentId }">
          <td>
            <span v-if="item.studentId == studentId"><b>You</b></span>
            <span v-else>Student {{ getStudentIndex(index) }}</span>
          </td>
          <td v-if="selectedComponent === 'total'">{{ item.total }}</td>
          <td v-else-if="selectedComponent === 'finalExam'">{{ item.finalExamMark }}</td>
          <td v-else>{{ item.marks?.[selectedComponent] || 0 }}</td>
        </tr>
      </tbody>
    </table>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script>
import { Bar } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js';
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

export default {
  components: { Bar },
  data() {
    const user = JSON.parse(sessionStorage.getItem('user'));

    return {
      studentId: user.studentId,
      courses: [],
      marks: [],
      maxFm: 0,
      components: [],
      selectedSectionId: null,
      comparisonData: [],
      selectedComponent: 'total',
      errorMessage: ''
    };
  },
  computed: {
    barChartData() {
      if (!this.marks.length) return { labels: [], datasets: [] };

      let count = 1;
      const labels = [];
      const data = [];

      this.marks.forEach(item => {
        const label = item.studentId === this.studentId ? 'You' : `Student ${count++}`;
        labels.push(label);

        let value = 0;
        if (this.selectedComponent === 'total') {
          value = Number(item.total);
        } else if (this.selectedComponent === 'finalExam') {
          value = Number(item.finalExamMark);
        } else {
          value = Number(item.marks?.[this.selectedComponent] || 0);
        }

        data.push(value);
      });

      const backgroundColor = this.marks.map(item =>
        item.studentId === this.studentId ? '#ffc107' : '#0d6efd'
      );

      return {
        labels,
        datasets: [
          {
            label:
              this.selectedComponent === 'total'
                ? 'Total Mark'
                : this.selectedComponent === 'finalExam'
                ? 'Final Exam'
                : this.selectedComponent,
            backgroundColor,
            data
          }
        ]
      };
    },
    barChartOptions() {
      return {
        responsive: true,
        plugins: {
          legend: { display: false },
          title: {
            display: true,
            text:
              this.selectedComponent === 'total'
                ? 'Anonymous Comparison: Total Marks'
                : this.selectedComponent === 'finalExam'
                  ? 'Anonymous Comparison: Final Exam'
                  : `Anonymous Comparison: ${this.selectedComponent}`
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            max: this.selectedComponent === 'total'
              ? 100
              : this.getComponentMax(this.selectedComponent)
              }
        }
      };
    }
  },
  async created(){
    await this.fetchCourses();
  },
  methods: {
    getComponentMax(name) {
      if (name === 'finalExam') return parseInt(this.maxFm) || 0;

      const comp = this.components.find(c => c.componentName === name);
      return comp ? parseInt(comp.maxMark) : 0;
    },
    getStudentIndex(index) {
      let count = 0;
      for (let i = 0; i <= index; i++) {
        if (this.marks[i].studentId !== this.studentId) {
          count++;
        }
      }
      return count;
    },
    async fetchCourses() {
      try {
        const res = await fetch(`http://localhost:3000/enrolled-courses?student_id=${this.studentId}`);
        if (!res.ok) {
          this.errorMessage = 'Server error loading courses';
          return;
        }
        this.courses = await res.json();
        // Auto-select first course if available
        if (this.courses.length && !this.selectedSectionId) {
          this.selectedSectionId = this.courses[0].sectionId;
          await this.fetchMarks();
        }
      } catch (err) {
        this.errorMessage = 'Failed to load courses.';
      }
    },
    async fetchMarks() {
      try {
        this.marks = [];
        const url = `http://localhost:3000/all_marks?section_id=${this.selectedSectionId}`;
        const res = await fetch(url);
        if (!res.ok) {
          this.errorMessage = 'Failed to load marks (server error).';
          return;
        }
        const result = await res.json();
        this.marks = (result.data || []).sort((a, b) => {
          return a.studentId === this.studentId ? -1 : b.studentId === this.studentId ? 1 : 0;
        });
        this.components = result.components || [];
        this.maxFm = result.maxFm || 0;
      } catch (err) {
        this.errorMessage = 'Failed to load marks (network error).';
      }
    },
    calculateCoursework(marksObj) {
      // Sum all component marks (object values)
      return Object.values(marksObj || {}).reduce((sum, val) => sum + Number(val), 0);
    }
  },
};
</script>

<style scoped>
h1 {
  font-size: 24px;
  margin-bottom: 20px;
}
.form-group {
  margin-bottom: 15px;
}
label {
  font-weight: bold;
}
select {
  width: 100%;
  padding: 8px;
  font-size: 16px;
}
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 20px;
}
th, td {
  border: 1px solid #ccc;
  padding: 10px;
  text-align: center;
}
th {
  background-color: #f5f5f5;
}
.error-message {
  color: red;
  margin-top: 10px;
}
.highlight {
  background-color: #fff3cd !important;
  font-weight: bold;
}
</style> 