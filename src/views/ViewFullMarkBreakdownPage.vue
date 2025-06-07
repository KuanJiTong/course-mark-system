<template>
  <DefaultLayout>
    <template #default>
      <div>
        <h2>Full Mark Breakdown</h2>
        
        <!-- Example: Table displaying full mark breakdown -->
        <table>
          <thead>
            <tr>
              <th>Student Name</th>
              <th>Assignments</th>
              <th>Quizzes</th>
              <th>Midterm</th>
              <th>Final Exam</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="student in students" :key="student.id">
              <td>{{ student.name }}</td>
              <td>{{ student.assignments }}%</td>
              <td>{{ student.quizzes }}%</td>
              <td>{{ student.midterm }}%</td>
              <td>{{ student.finalExam }}%</td>
              <td>{{ calculateTotal(student) }}%</td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </DefaultLayout>
</template>

<script>
import DefaultLayout from '../components/DefaultLayout.vue';  // Import DefaultLayout

export default {
  components: {
    DefaultLayout,  // Register DefaultLayout component
  },
  data() {
    return {
      // Sample data for students with mark breakdown for assignments, quizzes, midterm, and final exam
      students: [
        { 
          id: 1, 
          name: 'John Doe', 
          assignments: 85, 
          quizzes: 90, 
          midterm: 78, 
          finalExam: 88 
        },
        { 
          id: 2, 
          name: 'Jane Smith', 
          assignments: 92, 
          quizzes: 85, 
          midterm: 79, 
          finalExam: 91 
        },
        { 
          id: 3, 
          name: 'Mary Johnson', 
          assignments: 80, 
          quizzes: 76, 
          midterm: 88, 
          finalExam: 84 
        }
      ]
    };
  },
  methods: {
    // Method to calculate total marks (based on weights or sum of the components)
    calculateTotal(student) {
      const total = (student.assignments + student.quizzes + student.midterm + student.finalExam) / 4;
      return total.toFixed(2); // Round to 2 decimal places
    }
  }
};
</script>

<style scoped>
/* Your custom styles */
h2 {
  text-align: center;
  margin-bottom: 20px;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin: 20px 0;
}

table, th, td {
  border: 1px solid #ddd;
}

th, td {
  padding: 10px;
  text-align: left;
}

th {
  background-color: #f4f4f4;
}
</style>
