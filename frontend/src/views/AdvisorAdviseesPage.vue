<template>
  <div>
    <h2>My Advisees</h2>
    <table>
      <thead>
        <tr>
          <th>Student Name</th>
          <th>Matric No</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="student in advisees" :key="student.student_id">
          <td>{{ student.student_name }}</td>
          <td>{{ student.matric_no }}</td>
          <td>
            <button @click="goToMarksPage(student)">View Marks</button>
          </td>
        </tr>
      </tbody>
    </table>
    <div v-if="advisees.length === 0 && loaded">No advisees found.</div>
    <AdvisorAdviseeMarksPage
      v-if="selectedAdvisee"
      :studentId="selectedAdvisee.student_id"
      :studentName="selectedAdvisee.student_name"
    />
    <button v-if="selectedAdvisee" @click="selectedAdvisee = null" style="margin-top: 1rem;">Back to Advisees List</button>
  </div>
</template>

<script>
import AdvisorAdviseeMarksPage from './AdvisorAdviseeMarksPage.vue';

export default {
  name: 'AdvisorAdviseesPage',
  components: { AdvisorAdviseeMarksPage },
  data() {
    return {
      userID: 1,
      advisees: [],
      loaded: false,
      selectedAdvisee: null,
    };
  },
  computed: {
    advisorIdVar() {
      // Replace with actual logic to get logged-in advisor ID
      return 1;
    }
  },
  methods: {
    goToMarksPage(student) {
      this.$router.push({
        name: 'AdvisorAdviseeMarks',
        query: {
          studentId: student.student_id,
          studentName: student.student_name
        }
      });
    },
  },
  mounted() {
      fetch(`http://localhost:3000/advisor/advisees?advisor_id=${this.userID}`)
      .then(res => res.json())
      .then(data => {
        this.advisees = data;
        this.loaded = true;
      })
      .catch(() => {
        this.advisees = [];
        this.loaded = true;
      });
  },
};
</script>

<style scoped>
table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 1rem;
}
th, td {
  border: 1px solid #ddd;
  padding: 0.5rem 1rem;
  text-align: left;
}
th {
  background: #f5f5f5;
}
</style> 