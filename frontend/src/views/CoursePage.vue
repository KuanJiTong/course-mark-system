<template>
    <h2 class="mb-4">Course Management</h2>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <div class="d-flex gap-2 flex-wrap">
        <div>
          <button
            class="mb-2"
            :class="showForm ? 'btn btn-danger' : 'btn btn-success'"
            @click="showForm ? reset() : toggleForm()"
          >
            {{ showForm ? 'Cancel' : 'Add Course' }}
          </button>

          <div v-if="showForm" class="d-flex align-items-center gap-2 mb-3">
            <input type="text" v-model="newCourse.courseCode" placeholder="Course Code" class="form-control" style="width: 150px;">
            <input type="text" v-model="newCourse.courseName" placeholder="Course Name" class="form-control" style="width: 250px;">
            <input type="number" v-model="newCourse.credit" placeholder="Credit" class="form-control" style="width: 100px;">
            <input type="number" v-model="newCourse.numOfSections" placeholder="Sections" class="form-control" style="width: 100px;">
            <input type="number" v-model="newCourse.capacity" placeholder="Capacity" class="form-control" style="width: 100px;">
            <button class="btn btn-primary" @click="addCourse">Submit</button>
          </div>
        </div>
        <input
          v-model="searchQuery"
          @input = "searchCourses"
          type="text"
          class="form-control"
          placeholder="Search courses..."
        />
      </div>
    </div>

    <div class="table-responsive shadow-sm rounded">
      <table class="table table-bordered table-striped">
        <thead class="table-light">
          <tr>
            <th style="width: 50px;">#</th>
            <th style="width: 150px;">Course Code</th>
            <th style="width: 100%;">Course Name</th>
            <th style="width: 50px;">Credit</th>
            <th style="width: 50px;"># of Sec</th>
            <th style="width: 50px;">Carry Mark</th>
            <th style="width: 50px;">Final Mark</th>
            <th style="width: 200px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="courses.length === 0 && searchQuery" class="text-center text-muted">
            <td colspan="8">No courses found</td>
          </tr>
          <tr v-else-if="courses.length === 0" class="text-center text-muted">
            <td colspan="8">No courses added yet.</td>
          </tr>
          <tr v-for="(course, index) in courses" :key="index">
            <td>{{ index + 1 }}</td>
            <td>
              <span v-if="editingCourseId !== course.courseId">{{ course.courseCode }}</span>
              <input v-else style="width: 150px;" v-model="course.courseCode" type="text" />
            </td>
            <td>
              <span v-if="editingCourseId !== course.courseId">{{ course.courseName }}</span>
              <input v-else style="width: 100%;" v-model="course.courseName" type="text" />
            </td>
            <td>
              <span v-if="editingCourseId !== course.courseId">{{ course.credit }}</span>
              <input v-else style="width: 50px;" v-model.number="course.credit" type="number" />
            </td>
            <td>{{ course.numOfSections }}</td>
            <td>
              <span v-if="editingCourseId !== course.courseId">{{ course.maxCm }}</span>
              <input v-else style="width: 50px;" v-model.number="course.maxCm" type="number" />
            </td>
            <td>
              <span v-if="editingCourseId !== course.courseId">{{ course.maxFm }}</span>
              <input v-else style="width: 50px;" v-model.number="course.maxFm" type="number" />
            </td>

            <td class="text-center">
              <div class="icon-row">
                <!-- When NOT editing -->
                <template v-if="editingCourseId !== course.courseId">
                  <i class="bi bi-pencil-square text-primary mx-2" data-bs-toggle="tooltip" title="Edit" @click="startEdit(course)"></i>
                  <i class="bi bi-card-list text-secondary mx-2" data-bs-toggle="tooltip" title="View Sections" @click="viewSections(course)"></i>
                  <i class="bi bi-trash-fill text-danger mx-2" data-bs-toggle="tooltip" title="Delete" @click="deleteCourse(course.courseId)"></i>
                </template>

                <!-- When editing -->
                <template v-else>
                  <button class="btn btn-sm btn-primary mx-1" @click="updateCourse(course)">Submit</button>
                  <button class="btn btn-sm btn-danger mx-1" @click="cancelEdit(course)">Cancel</button>
                </template>
              </div>
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
      facultyId: 1,
      editingCourseId: null,
      newCourse: {
        courseCode: '',
        courseName: '',
        credit: null,
        numOfSections: null,
        capacity: null,
        faculty: null
      },
      courses: [],
      showForm: false,
      searchQuery: "",
    };
  },
  async created(){
    await this.fetchAllCourses();
  },
  methods: {
    reset(){
      this.newCourse.courseCode = '';
      this.newCourse.courseName = '';
      this.newCourse.numOfSections = null;
      this.newCourse.capacity = null;
      this.newCourse.credit = null;
      this.showForm = false;
    },
    toggleForm(){
      this.showForm = true;
    },
    async fetchAllCourses(){
      try {
        const facultyId = this.facultyId; 
        const url = `http://localhost:3000/course?faculty_id=${facultyId}`;

        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch courses');

        const data = await response.json();
        this.courses = data; 
      } catch (error) {
        console.error('Error fetching courses:', error);
      }
    },
    async searchCourses() {
      try {
        const facultyId = this.facultyId;

        const trimmedKeyword = this.searchQuery.trim();
        if (trimmedKeyword === '') {
          await this.fetchAllCourses();
          return;
        }

        const url = `http://localhost:3000/course?faculty_id=${facultyId}&keyword=${encodeURIComponent(trimmedKeyword)}`;
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch courses');

        const data = await response.json();
        this.courses = data;
      } catch (error) {
        console.error('Error searching courses:', error);
      }
    },

    startEdit(course) {
      if (this.editingCourseId) {
        const prevCourse = this.courses.find(c => c.courseId === this.editingCourseId);
        if (prevCourse && prevCourse._backup) {
          Object.assign(prevCourse, prevCourse._backup);
          delete prevCourse._backup;
        }
      }
      this.editingCourseId = course.courseId;
      course._backup = { ...course };
    },
    cancelEdit(course) {
      if (course._backup) {
        Object.assign(course, course._backup);
        delete course._backup;
      }
      this.editingCourseId = null;
    },
    viewSections(course) {
      this.$router.push({ name: 'SectionPage', params: { courseId: course.courseId} });
    },
    async updateCourse(course){
      this.editingCourseId = null;
      delete course._backup;
      delete course.numOfSections; 
      try {
        const response = await fetch(`http://localhost:3000/course/${course.courseId}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(course),
        });

        if (!response.ok) {
          const error = await response.json();
          console.error('Update failed:', error);
          alert('Failed to update course.');
        } else {
          alert('Course updated successfully.');
          this.fetchAllCourses();
        }
      } catch (err) {
        console.error('Request error:', err);
        alert('Network error.');
      }
    },
    async addCourse() {
      // Prepare the data to be sent
      this.newCourse.faculty = this.facultyId;
      const newCourse = this.newCourse;

      // Send POST request
      await fetch('http://localhost:3000/course', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(newCourse)
      })
      .then(async response => {
        if (!response.ok) {
          throw new Error('Failed to add course');
        }
        return await response.json();
      })
      .then(data => {
        alert('Course added:', data);

        this.reset();

        // Fetch the updated course list
        this.fetchAllCourses();
      })
      .catch(error => {
        console.error('Error:', error);
      });
    },
    async deleteCourse(courseId) {
      if (!confirm('Are you sure you want to delete this course?')) return;

      try {
        const response = await fetch(`http://localhost:3000/course/${courseId}`, {
          method: 'DELETE',
          headers: {
            'Content-Type': 'application/json',
          },
        });

        const result = await response.json();

        if (response.ok) {
          alert(result.message || 'Course deleted successfully.');
          this.fetchAllCourses();
        } else {
          alert(result.error || 'Failed to delete course.');
        }
      } catch (error) {
        console.error('Delete error:', error);
        alert('An error occurred while deleting.');
      }
    }
  },
};
</script>

<style scoped>
.table-responsive {
  background-color: #fff;
}

.icon-row {
  display: flex;
  justify-content: center;
  gap: 3px; 
}

.bi{
  cursor: pointer;
  width: 25px;
  height: 25px;
  border-radius: 50%;
  background-color: transparent;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: background-color 0.3s ease;
}

.bi:hover {
  background-color: #b5b3b368; 
}

.bi:active {
  transform: scale(0.9); 
  background-color: #e0e0e0; 
}
</style>
