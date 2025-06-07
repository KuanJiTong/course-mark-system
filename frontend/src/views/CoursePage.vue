<template>
    <h2 class="mb-4">Course Management</h2>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-success" @click="showForm = !showForm">
          {{ showForm ? 'Cancel' : 'Add Course' }}
        </button>
        <input
          v-model="searchQuery"
          type="text"
          class="form-control"
          placeholder="Search courses..."
        />
      </div>
    </div>

    <form v-if="showForm" @submit.prevent="addCourse" class="d-flex gap-2 mb-4">
      <input
        v-model="newCourse"
        type="text"
        class="form-control"
        placeholder="Enter course name"
        required
      />
      <button type="submit" class="btn btn-primary">Save</button>
    </form>

    <div class="table-responsive shadow-sm rounded">
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th style="width: 50px;">#</th>
            <th>Course Name</th>
            <th style="width: 100px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="courses.length === 0" class="text-center text-muted">
            <td colspan="3">No courses added yet.</td>
          </tr>
          <tr v-for="(course, index) in courses" :key="index">
            <td>{{ index + 1 }}</td>
            <td>{{ course }}</td>
            <td class="text-center">
              <button class="btn btn-sm btn-danger" @click="deleteCourse(index)">
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
</template>

<script>
export default {
  name: "CourseManagement",
  data() {
    return {
      newCourse: "",
      courses: [],
      showForm: false,
      searchQuery: "",
    };
  },
  methods: {
    addCourse() {
      const trimmed = this.newCourse.trim();
      if (trimmed) {
        this.courses.push(trimmed);
        this.newCourse = "";
      }
    },
    deleteCourse(index) {
      this.courses.splice(index, 1);
    },
  },
};
</script>

<style scoped>
.table-responsive {
  background-color: #fff;
}
</style>
