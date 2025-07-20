<template>
  <div>
    <h2 class="mt-4 mb-4">My Advisees</h2>
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
            <button class="btn btn-primary btn-sm" @click="goToMarksPage(student)">View Marks</button>
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
    <button v-if="selectedAdvisee" class="btn btn-secondary mt-3" @click="selectedAdvisee = null">Back to Advisees List</button>
  </div>
</template>

<script>
import AdvisorAdviseeMarksPage from './AdvisorAdviseeMarksPage.vue';

export default {
  name: 'AdvisorAdviseesPage',
  components: { AdvisorAdviseeMarksPage },
  data() {
    return {
      userID: null,
      advisorID: null,
      advisees: [],
      loaded: false,
      selectedAdvisee: null,
    };
  },
  // computed: {
  //   advisorIdVar() {
  //     return this.userID;
  //   }
  // },
  methods: {
    getAuthenticatedUser() {
      const userData = sessionStorage.getItem('user');
      if (userData) {
        const user = JSON.parse(userData);
        this.userID = user.user_id;
        this.advisorID = user.lecturerId;
        console.log('Authenticated user ID:', this.userID, 'Advisor (lecturer) ID:', this.advisorID);
        return true;
      }
      return false;
    },
    goToMarksPage(student) {
      this.$router.push({
        name: 'AdvisorAdviseeMarks',
        query: {
          studentId: student.student_id,
          studentName: student.student_name
        }
      });
    },
    async fetchAdvisees() {
      try {
        const res = await fetch(`http://localhost:3000/advisor/advisees?advisor_id=${this.advisorID}`);
        if (!res.ok) {
          throw new Error('Failed to fetch advisees');
        }
        const data = await res.json();
        this.advisees = data;
        this.loaded = true;
      } catch (error) {
        console.error('Error fetching advisees:', error);
        this.advisees = [];
        this.loaded = true;
      }
    }
  },
  mounted() {
    if (this.getAuthenticatedUser()) {
      this.fetchAdvisees();
    } else {
      console.error('Authentication required. Please login.');
      this.$router.push('/login');
    }
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