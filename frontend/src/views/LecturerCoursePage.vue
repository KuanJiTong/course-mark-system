<template>
    <h2 class="mb-4">Lecturer Course</h2>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <div class="d-flex gap-2 flex-wrap">
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
            <th style="width: 50px;">Section</th>
            <th style="width: 50px;">Credit</th>
            <th style="width: 50px;">Capacity</th>
            <th style="width: 50px;"># of students</th>
            <th style="width: 50px;">Max. Carry Mark</th>
            <th style="width: 50px;">Max. Final Mark</th>
            <th style="width: 200px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="courses.length === 0 && searchQuery" class="text-center text-muted">
            <td colspan="7">No courses found</td>
          </tr>
          <tr v-else-if="courses.length === 0" class="text-center text-muted">
            <td colspan="7">No courses added yet.</td>
          </tr>
          <tr v-for="(course, index) in courses" :key="index">
            <td>{{ index + 1 }}</td>
            <td>
              <span>{{ course.courseCode }}</span>
            </td>
            <td>
              <span>{{ course.courseName }}</span>
            </td>
            <td>
              <span>{{ course.sectionNumber }}</span>
            </td>
            <td>
              <span>{{ course.credit }}</span>
            </td>
            <td>
              <span>{{ course.capacity }}</span>
            </td>
            <td>
              <span>{{ course.studentCount }}</span>
            </td>
            <td>
              <span>{{ course.maxCm }}</span>
            </td>
            <td>
              <span>{{ course.maxFm }}</span>
            </td>

            <td class="text-center">
              <div class="icon-row">
                <i class="bi bi-people-fill text-primary mx-2" data-bs-toggle="tooltip" title="View Students" @click="viewStudents(course.sectionId)"></i>
                <i class="bi bi-card-list text-secondary mx-2" data-bs-toggle="tooltip" title="View Sections" @click="viewSections(course)"></i>
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
      lecturerId: null,
      courses: [],
      searchQuery: "",
    };
  },
  async created(){
    // Check authentication
    const user = JSON.parse(sessionStorage.getItem('user'));
    if (!user || !user.user_id) {
      this.$router.push('/login?message=Please login to access lecturer courses');
      return;
    }
    
    this.lecturerId = user.user_id;
    console.log('Authenticated lecturer ID:', this.lecturerId);
    
    await this.fetchAllLecturerCourses();
  },
  methods: {
    async fetchAllLecturerCourses(){
      try {
        if (!this.lecturerId) {
          console.error('No lecturer ID available');
          return;
        }
        
        const url = `http://localhost:3000/lecturer-course/${this.lecturerId}`;

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
        if (!this.lecturerId) {
          console.error('No lecturer ID available');
          return;
        }

        const trimmedKeyword = this.searchQuery.trim();
        if (trimmedKeyword === '') {
          await this.fetchAllLecturerCourses();
          return;
        }

        const url = `http://localhost:3000/course?faculty_id=${this.lecturerId}&keyword=${encodeURIComponent(trimmedKeyword)}`;
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to fetch courses');

        const data = await response.json();
        this.courses = data;
      } catch (error) {
        console.error('Error searching courses:', error);
      }
    },
    viewStudents(sectionId) {
      this.$router.push({ name: 'StudentEnrollment', params: { sectionId: sectionId} });
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
