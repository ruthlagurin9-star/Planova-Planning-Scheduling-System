import { useAuth } from "../context/AuthContext";

export default function Dashboard() {
  const { user, logout } = useAuth();

  async function handleLogout() {
    try {
      await logout();
    } finally {
      window.location.href = "/";
    }
  }

  return (
    <main className="dashboard-page">
      <header className="dashboard-header">
        <div>
          <h1>Planova Dashboard</h1>
          <p>Welcome, {user?.name || "User"}!</p>
        </div>

        <button onClick={handleLogout}>Logout</button>
      </header>

      <section className="dashboard-cards">
        <article className="stat-card">
          <h3>Schedules</h3>
          <p>View and manage your schedules.</p>
        </article>

        <article className="stat-card">
          <h3>Tasks</h3>
          <p>Track your tasks and deadlines.</p>
        </article>

        <article className="stat-card">
          <h3>Categories</h3>
          <p>Organize your activities.</p>
        </article>
      </section>
    </main>
  );
}
