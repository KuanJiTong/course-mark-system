<template>
  <div class="mark-breakdown">
    <h1>View Full Mark Breakdown</h1>

    <!-- Select Course and Section -->
    <div class="form-group">
      <label for="course">Course:</label>
      <select v-model="selectedCourseId" @change="fetchSections" required>
        <option disabled value="">-- Select Course --</option>
        <option v-for="course in courses" :key="course.course_id" :value="course.course_id">
          {{ course.course_name }}
        </option>
      </select>
    </div>

    <div class="form-group" v-if="sections.length">
      <label for="section">Section:</label>
      <select v-model="selectedSectionId" @change="fetchMarks" required>
        <option disabled value="">-- Select Section --</option>
        <option v-for="section in sections" :key="section.section_id" :value="section.section_id">
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
          <th>Coursework (70%)</th>
          <th>Final Exam (30%)</th>
          <th>Total Mark</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in marks" :key="item.student_id">
          <td>{{ item.student_id }}</td>
          <td>{{ item.student_name }}</td>
          <td>{{ item.coursework_mark }}</td>
          <td>{{ item.final_exam_mark }}</td>
          <td>{{ calculateTotal(item.coursework_mark, item.final_exam_mark) }}</td>
        </tr>
      </tbody>
    </table>
    <!-- Export CSV Button -->
    <button v-if="marks.length" @click="exportCSV" class="export-btn">
    Export Marks as CSV
    </button>
    <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      courses: [],
      sections: [],
      marks: [],
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
            const url = `http://localhost:3000/all_marks?course_id=${this.selectedCourseId}&section_id=${this.selectedSectionId}`;
            console.log('Fetching marks from:', url); // ✅ log the URL
            const res = await fetch(url);

            if (!res.ok) {
            const text = await res.text();
            console.error('Server responded with error:', text); // ✅ log error details
            this.errorMessage = 'Failed to load marks (server error).';
            return;
            }

            this.marks = await res.json();
        } catch (err) {
            console.error('Fetch failed:', err); // ✅ log JS/network errors
            this.errorMessage = 'Failed to load marks (network error).';
        }
        },
    calculateTotal(coursework, finalExam) {
      return ((coursework) + (finalExam)).toFixed(2);
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
    }
  },
  mounted() {
    this.fetchCourses();
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
</style>
