import React from 'react';
import { BrowserRouter, Routes, Route, Navigate, Outlet } from 'react-router-dom';
import { useState, useEffect } from 'react';
import { LucideLayoutDashboard, LucideBriefcase, LucideCheckSquare, LucidePackage, LucideUsers, LucideBell, LucideLogOut, LucideMenu, LucideFileText, LucideAlertCircle, LucideMessageSquare } from 'lucide-react';

import HomeLanding from './pages/HomeLanding';
import Dashboard from './pages/Dashboard';
import Login from './pages/Login';
import Projects from './pages/Projects';
import Tasks from './pages/Tasks';
import Materials from './pages/Materials';
import Workers from './pages/Workers';
import Reports from './pages/Reports';
import Messages from './pages/Messages';

function ProtectedRoute({ children, isAuthenticated }: { children: React.ReactNode, isAuthenticated: boolean }) {
  if (!isAuthenticated) return <Navigate to="/login" replace />;
  return <>{children}</>;
}

function Layout({ onLogout }: { onLogout: () => void }) {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const [notifications, setNotifications] = useState<any[]>([]);
  const [showNotifications, setShowNotifications] = useState(false);
  
  useEffect(() => {
    const fetchNotifications = async () => {
      const token = localStorage.getItem('token');
      if (token) {
        const res = await fetch('/api/notifications', { headers: { 'Authorization': `Bearer ${token}` } });
        if (res.ok) setNotifications(await res.json());
      }
    };
    fetchNotifications();
    const interval = setInterval(fetchNotifications, 60000); // Polling every minute
    return () => clearInterval(interval);
  }, []);

  const markAsRead = async (id: number) => {
    const token = localStorage.getItem('token');
    await fetch(`/api/notifications/${id}/read`, { method: 'PUT', headers: { 'Authorization': `Bearer ${token}` } });
    setNotifications(notifications.map(n => n.id === id ? { ...n, is_read: 1 } : n));
  };

  const unreadCount = notifications.filter(n => n.is_read === 0).length;
  
  const navItems = [
    { name: 'Dashboard', path: '/dashboard', icon: LucideLayoutDashboard },
    { name: 'Projects', path: '/dashboard/projects', icon: LucideBriefcase },
    { name: 'Tasks', path: '/dashboard/tasks', icon: LucideCheckSquare },
    { name: 'Mafundi & Equipment', path: '/dashboard/workers', icon: LucideUsers },
    { name: 'Materials', path: '/dashboard/materials', icon: LucidePackage },
    { name: 'Discussions', path: '/dashboard/messages', icon: LucideMessageSquare },
    { name: 'Reports', path: '/dashboard/reports', icon: LucideFileText },
  ];

  return (
    <div className="min-h-screen bg-gray-50 flex">
      {/* Mobile sidebar overlay */}
      {sidebarOpen && (
        <div className="fixed inset-0 z-20 bg-black/50 lg:hidden" onClick={() => setSidebarOpen(false)} />
      )}
      
      {/* Sidebar */}
      <aside className={`fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 text-white transform transition-transform duration-300 lg:translate-x-0 lg:static lg:inset-auto ${sidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}>
        <div className="flex items-center justify-center h-16 border-b border-gray-800">
          <h1 className="text-xl font-bold tracking-wider">SMART UJENZI</h1>
        </div>
        <nav className="p-4 space-y-2">
          {navItems.map((item) => (
            <a key={item.name} href={item.path} onClick={(e) => { e.preventDefault(); window.location.href = item.path; }} className="flex items-center p-3 rounded-lg hover:bg-slate-800 text-gray-300 hover:text-white transition-colors">
              <item.icon className="w-5 h-5 mr-3" />
              {item.name}
            </a>
          ))}
          <button onClick={onLogout} className="flex items-center p-3 rounded-lg hover:bg-red-900/50 text-red-400 w-full text-left transition-colors mt-8">
            <LucideLogOut className="w-5 h-5 mr-3" />
            Logout
          </button>
        </nav>
      </aside>

      {/* Main Content */}
      <div className="flex-1 flex flex-col min-w-0">
        <header className="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 lg:px-8">
          <button onClick={() => setSidebarOpen(true)} className="lg:hidden p-2 text-gray-500 hover:bg-gray-100 rounded-lg">
            <LucideMenu className="w-6 h-6" />
          </button>
          <div className="flex items-center space-x-4 ml-auto relative">
            <button onClick={() => setShowNotifications(!showNotifications)} className="p-2 text-gray-500 hover:bg-gray-100 rounded-full relative">
              <LucideBell className="w-5 h-5" />
              {unreadCount > 0 && (
                <span className="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
              )}
            </button>
            
            {/* Notifications Dropdown */}
            {showNotifications && (
              <div className="absolute top-12 right-12 w-80 bg-white border border-gray-200 rounded-lg shadow-xl z-50 max-h-96 flex flex-col">
                <div className="p-3 border-b border-gray-100 font-bold text-gray-800 flex justify-between items-center">
                  <span>Notifications</span>
                  {unreadCount > 0 && <span className="bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full">{unreadCount} unread</span>}
                </div>
                <div className="overflow-y-auto flex-1">
                  {notifications.length === 0 ? (
                    <div className="p-4 text-center text-gray-500 text-sm">No notifications</div>
                  ) : (
                    notifications.map(n => (
                      <div key={n.id} onClick={() => markAsRead(n.id)} className={`p-3 border-b border-gray-50 cursor-pointer hover:bg-gray-50 transition-colors ${n.is_read ? 'opacity-50' : 'bg-blue-50/30'}`}>
                        <div className="flex items-start">
                          <LucideAlertCircle className={`w-4 h-4 mr-2 mt-0.5 ${n.is_read ? 'text-gray-400' : 'text-blue-500'}`} />
                          <div>
                            <p className={`text-sm ${n.is_read ? 'text-gray-600' : 'text-gray-800 font-medium'}`}>{n.message}</p>
                            <span className="text-xs text-gray-400">{new Date(n.created_at).toLocaleString()}</span>
                          </div>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>
            )}
            
            <div className="w-8 h-8 rounded-full bg-slate-800 text-white flex items-center justify-center font-semibold text-sm">
              AD
            </div>
          </div>
        </header>

        <main className="flex-1 p-4 lg:p-8 overflow-y-auto relative z-0">
          <Outlet />
        </main>
      </div>
    </div>
  );
}

export default function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const token = localStorage.getItem('token');
    if (token) {
      setIsAuthenticated(true);
    }
    setLoading(false);
  }, []);

  const handleLogin = (token: string) => {
    localStorage.setItem('token', token);
    setIsAuthenticated(true);
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    setIsAuthenticated(false);
  };

  if (loading) return <div>Loading...</div>;

  return (
    <BrowserRouter>
      <Routes>
        <Route path="/" element={<HomeLanding />} />
        <Route path="/login" element={<Login onLogin={handleLogin} isAuthenticated={isAuthenticated} />} />
        
        <Route element={<ProtectedRoute isAuthenticated={isAuthenticated}><Layout onLogout={handleLogout} /></ProtectedRoute>}>
          <Route path="/dashboard" element={<Dashboard />} />
          <Route path="/dashboard/projects" element={<Projects />} />
          <Route path="/dashboard/tasks" element={<Tasks />} />
          <Route path="/dashboard/materials" element={<Materials />} />
          <Route path="/dashboard/workers" element={<Workers />} />
          <Route path="/dashboard/messages" element={<Messages />} />
          <Route path="/dashboard/reports" element={<Reports />} />
        </Route>
      </Routes>
    </BrowserRouter>
  );
}
