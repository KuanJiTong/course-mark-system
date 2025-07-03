<template>
  <div class="mark-breakdown">
    <h1>View Full Mark Breakdown</h1>

    <!-- Select Course and Section -->
    <div class="form-group">
      <label for="course">Course:</label>
      <select v-model="selectedCourseId" @change="fetchSections" required>
        <option disabled value="">-- Select Course --</option>
        <option
          v-for="course in courses"
          :key="course.course_id"
          :value="course.course_id"
        >
          {{ course.course_name }}
        </option>
      </select>
    </div>

    <div class="form-group" v-if="sections.length">
      <label for="section">Section:</label>
      <select v-model="selectedSectionId" @change="fetchMarks" required>
        <option disabled value="">-- Select Section --</option>
        <option
          v-for="section in sections"
          :key="section.section_id"
          :value="section.section_id"
        >
          Section {{ section.section_number }}
        </option>
      </select>
    </div>

    <!-- Table Display -->
    <table v-if="marks.length">
      <thead>
        <tr>
          <th>Student ID</th>
          <th>Name</th>
          <th v-for="comp in components" :key="comp">{{ comp }}</th>
          <th>Final Exam</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="student in marks" :key="student.student_id">
          <td>{{ student.student_id }}</td>
          <td>{{ student.student_name }}</td>
          <td v-for="comp in components" :key="comp">
            {{ student.marks[comp] ?? 0 }}
          </td>
          <td>{{ formatMark(student.final_exam_mark) }}</td>
          <td>{{ student.total.toFixed(2) }}</td>
        </tr>
      </tbody>
    </table>

    <!-- 📊 Student Performance Charts -->
    <div class="student-charts" v-if="marks.length">
      <StudentProgressChart
  v-for="student in marks"
  :key="student.student_id + '-chart'"
  :studentName="student.student_name"
  :components="[...components, 'Final Exam']"
  :marks="{ ...student.marks, 'Final Exam': student.final_exam_mark }"
/>

<!-- Pie Charts -->
<div class="student-pie-charts" v-if="marks.length">
  <StudentPieChart
    v-for="student in marks"
    :key="student.student_id + '-pie'"
    :studentName="student.student_name"
    :components="[...components, 'Final Exam']"
    :marks="{ ...student.marks, 'Final Exam': student.final_exam_mark }"
  />
</div>
    </div>

    <!-- Export CSV Button -->
    <button v-if="marks.length" @click="exportCSV" class="export-btn">
      Export Marks as CSV
    </button>

    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>


<script>
import StudentProgressChart from '@/components/StudentProgressChart.vue';
import StudentPieChart from '@/components/StudentPieChart.vue';
export default {
  components: {
    // StudentProgressChart,
    StudentProgressChart,
    StudentPieChart
  },
  data() {
    return {
      courses: [],
      sections: [],
      marks: [],
      components: [],
      selectedCourseId: '',
      selectedSectionId: '',
      errorMessage: ''
    };
  },
  methods: {
    async fetchCourses() {
        try {
            const res = await fetch('http://localhost:3000/courses');
            if (!res.ok) {
            const text = await res.text();
            console.error('Error response from server:', text);
            this.errorMessage = 'Server error loading courses';
            return;
            }
            this.courses = await res.json();
        } catch (err) {
            console.error('Fetch error:', err);
            this.errorMessage = 'Failed to load courses.';
        }
        },
    async fetchSections() {
      try {
        this.sections = [];
        const res = await fetch(`http://localhost:3000/sections?course_id=${this.selectedCourseId}`);
        this.sections = await res.json();
      } catch {
        this.errorMessage = 'Failed to load sections.';
      }
    },
    async fetchMarks() {
  try {
    this.marks = [];
    const res = await fetch(`http://localhost:3000/all_marks?course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`);
    const data = await res.json();

    this.components = data.components; // like ['quiz', 'assignment']
    this.marks = data.data;            // array of students
  } catch (err) {
    this.errorMessage = 'Failed to load marks';
  }
},
    calculateTotal(coursework, finalExam) {
      const cw = Number(coursework) || 0;
      const fe = Number(finalExam) || 0;
      const maxCM = this.maxCourseworkMark;
      const maxFM = this.maxFinalExamMark;

      const totalMax = maxCM + maxFM;
      if (totalMax === 0) return '0.00';

      const total = ((cw / maxCM) * maxCM + (fe / maxFM) * maxFM) / totalMax * 100;
      return total.toFixed(2);
    },
    exportCSV() {
        if (!this.selectedCourseId || !this.selectedSectionId) {
        alert("Please select both course and section.");
        return;
        }

        const url = `http://localhost:3000/all_marks_csv?course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`;

        const link = document.createElement('a');
        link.href = url;
        link.setAttribute(
        'download',
        `marks_course_${this.selectedCourseId}_section_${this.selectedSectionId}.csv`
        );
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    },
    formatMark(mark) {
    const num = Number(mark);
    return isNaN(num) ? '0.00' : num.toFixed(2);
  }
  },
  mounted() {
    this.fetchCourses();
  },
  computed: {
  selectedCourse() {
    return this.courses.find(c => c.course_id === this.selectedCourseId) || null;
  },
  maxCourseworkMark() {
    return this.selectedCourse?.max_cm || 0;
  },
  maxFinalExamMark() {
    return this.selectedCourse?.max_fm || 0;
  },
  courseworkWeight() {
    const total = this.maxCourseworkMark + this.maxFinalExamMark;
    return total > 0 ? ((this.maxCourseworkMark / total) * 100).toFixed(0) : 0;
  },
  finalExamWeight() {
    const total = this.maxCourseworkMark + this.maxFinalExamMark;
    return total > 0 ? ((this.maxFinalExamMark / total) * 100).toFixed(0) : 0;
  }
}
};
</script>

<style scoped>
.mark-breakdown {
  max-width: 800px;
  margin: auto;
  padding: 20px;
}
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
.student-charts {
  margin-top: 30px;
  display: flex;
  flex-direction: column;
  gap: 25px;
}
.student-pie-charts {
  display: flex;
  flex-wrap: wrap;
  gap: 30px;
  justify-content: center;
}

</style>
