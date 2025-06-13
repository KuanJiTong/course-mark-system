<template>
  <div v-if="show" class="modal d-block" tabindex="-1" role="dialog" style="background: rgba(0,0,0,0.5);">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
      <div class="modal-content rounded-3">
        <div class="modal-header text-center">
          <h5 class="modal-title w-100">Assign Students</h5>
          <button type="button" class="btn-close" aria-label="Close" @click="$emit('close')"></button>
        </div>
        <div class="modal-body p-4" style="overflow-y: auto; max-height: 500px;">
          <div class="mb-3">
            <input
              v-model="searchQuery"
              type="text"
              class="form-control mb-2"
              placeholder="Search by name or matric no..."
              @input="searchStudent"
            />
            <div class="table-responsive shadow-sm rounded">
              <table class="table table-bordered table-striped">
                <thead class="table-light">
                  <tr>
                    <th scope="col">Select</th>
                    <th scope="col">Matric No</th>
                    <th scope="col">Name</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="student in students" :key="student.studentId">
                    <td class="text-center" @click="toggleEnroll(Number(student.studentId))">
                      <input
                        type="checkbox"
                        class="form-check-input"
                        :value="Number(student.studentId)"
                        v-model="enrollList"
                      />
                    </td>
                    <td>{{ student.matricNo }}</td>
                    <td>{{ student.studentName }}</td>
                  </tr>
                  <tr v-if="students.length === 0">
                    <td colspan="3" class="text-center text-muted">No students found</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" @click="$emit('close')">Cancel</button>
          <button type="submit" class="btn btn-primary" @click="handleSubmit">
            Submit
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    show: Boolean,
    sectionId: Number
  },
  data() {
    return {
      students: [],
      searchQuery: '',
      enrollList:[]
    }
  },
  async created(){
    await this.fetchAllStudents();
    
  },
  methods: {
    async fetchAllStudents(){
      const sectionId = this.sectionId;
      try {
        const url = `http://localhost:3000/students/${sectionId}`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch students');

        const data = await response.json();
        this.students = data; 
      } catch (error) {
        console.error('Error fetching students:', error);
      }
    },
    toggleEnroll(studentId) {
      console.log(this.enrollList);
      const index = this.enrollList.indexOf(studentId);
      if (index === -1) {
        this.enrollList.push(studentId);
      } else {
        this.enrollList.splice(index, 1);
      }
    },
    async searchStudent() {
      const sectionId = this.sectionId;
      try {
        const trimmedKeyword = this.searchQuery.trim();
        if (trimmedKeyword === '') {
          await this.fetchAllStudents();
          return;
        }

        const url = `http://localhost:3000/students/${sectionId}?keyword=${encodeURIComponent(trimmedKeyword)}`;
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch students');

        const data = await response.json();
        this.students = data;
      } catch (error) {
        console.error('Error searching students:', error);
      }
    },
    handleSubmit() {
      this.$emit('submit-enrollment', this.enrollList);
    },
  }
};
</script>

<style scoped>
.form-check-input{
  border: var(--bs-border-width) solid #000;
}
</style>
