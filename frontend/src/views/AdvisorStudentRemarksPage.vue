<template>
  <div class="advisor-student-remarks">
    <h1>Student Feedback / Remark Requests</h1>
    <div class="form-group">
      <label for="course">Course:</label>
      <select v-model="selectedCourseId" @change="fetchSections">
        <option value="">-- All Courses --</option>
        <option v-for="course in courses" :key="course.course_id" :value="course.course_id">
          {{ course.course_name }}
        </option>
      </select>
    </div>
    <div class="form-group" v-if="sections.length">
      <label for="section">Section:</label>
      <select v-model="selectedSectionId" @change="fetchRemarks">
        <option value="">-- All Sections --</option>
        <option v-for="section in sections" :key="section.section_id" :value="section.section_id">
          Section {{ section.section_number }}
        </option>
      </select>
    </div>
    <table v-if="remarks.length">
      <thead>
        <tr>
          <th>Date</th>
          <th>Advisee</th>
          <th>Course</th>
          <th>Section</th>
          <th>Component</th>
          <th>Justification</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="remark in remarks" :key="remark.request_id">
          <td>{{ new Date(remark.created_at).toLocaleString() }}</td>
          <td>{{ getAdviseeName(remark.student_id) }}</td>
          <td>{{ remark.course_id }}</td>
          <td>{{ remark.section_id }}</td>
          <td>{{ remark.component_name || '-' }}</td>
          <td>{{ remark.justification }}</td>
          <td>{{ remark.status }}</td>
        </tr>
      </tbody>
    </table>
    <div v-if="!remarks.length && loaded">No feedback/remarks found for your advisees.</div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      userID: 1, 
      courses: [],
      sections: [],
      remarks: [],
      advisees: [],
      selectedCourseId: '',
      selectedSectionId: '',
      loaded: false,
    };
  },
  methods: {
    async fetchCourses() {
      const res = await fetch('http://localhost:3000/courses');
      this.courses = await res.json();
    },
    async fetchSections() {
      if (!this.selectedCourseId) {
        this.sections = [];
        this.fetchRemarks();
        return;
      }
      const res = await fetch(`http://localhost:3000/sections?course_id=${this.selectedCourseId}`);
      this.sections = await res.json();
      this.fetchRemarks();
    },
    async fetchAdvisees() {
      const res = await fetch(`http://localhost:3000/advisor/advisees?advisor_id=${this.userID}`);
      this.advisees = await res.json();
    },
    async fetchRemarks() {
      this.remarks = [];
      this.loaded = false;
      // Fetch all remarks for all advisees
      const allRemarks = [];
      for (const advisee of this.advisees) {
        let url = `http://localhost:3000/student/remark-requests?student_id=${advisee.student_id}`;
        const res = await fetch(url);
        const data = await res.json();
        // Optionally filter by course/section
        const filtered = data.filter(r =>
          (!this.selectedCourseId || r.course_id == this.selectedCourseId) &&
          (!this.selectedSectionId || r.section_id == this.selectedSectionId)
        );
        for (const remark of filtered) {
          allRemarks.push({ ...remark, student_id: advisee.student_id });
        }
      }
      this.remarks = allRemarks.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      this.loaded = true;
    },
    getAdviseeName(student_id) {
      const found = this.advisees.find(a => a.student_id == student_id);
      return found ? found.student_name : 'Advisee';
    }
  },
  async mounted() {
    await this.fetchCourses();
    await this.fetchAdvisees();
    await this.fetchRemarks();
  }
};
</script>

<style scoped>
.advisor-student-remarks {
  max-width: 900px;
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
</style> 