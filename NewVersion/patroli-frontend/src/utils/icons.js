/**
 * Resolves a menu icon from the `ico` field (stored as FA class names in the DB)
 * to a Lucide Vue component. Falls back to a deterministic icon based on menu ID.
 */
import {
  LayoutDashboard, Home, Grid, Layers,
  Shield, ShieldCheck, Lock, Key, Eye, Fingerprint, Camera, QrCode,
  Users, User, UserCog,
  Map, MapPin, Navigation,
  Calendar, CalendarDays, Clock,
  BarChart2, BarChart3, LineChart, PieChart, TrendingUp,
  Settings, Settings2,
  FileText, Clipboard, ClipboardList, ClipboardCheck, File,
  List, ListChecks, Database, Building2, Briefcase,
  Bell, Phone, Mail,
  Info, AlertTriangle, AlertCircle, CheckCircle2,
  Tag, Search, Activity, Monitor, BookOpen, Table2,
  Package, Boxes, Layers2,
} from 'lucide-vue-next'

/* ----------------------------------------------------------------
   Name → component map
   Keys: lowercase, hyphens kept, fa-/fa prefixes stripped
   ---------------------------------------------------------------- */
const iconMap = {
  // Navigation / layout
  home:               Home,
  dashboard:          LayoutDashboard,
  tachometer:         LayoutDashboard,
  'tachometer-alt':   LayoutDashboard,
  grid:               Grid,
  layers:             Layers,

  // Security / patrol
  shield:             Shield,
  'shield-alt':       Shield,
  'shield-check':     ShieldCheck,
  lock:               Lock,
  key:                Key,
  eye:                Eye,
  fingerprint:        Fingerprint,
  camera:             Camera,
  'camera-retro':     Camera,
  qrcode:             QrCode,

  // People
  user:               User,
  users:              Users,
  group:              Users,
  'user-cog':         UserCog,
  'users-cog':        UserCog,
  'user-gear':        UserCog,

  // Location
  map:                Map,
  'map-marker':       MapPin,
  'map-marker-alt':   MapPin,
  'map-pin':          MapPin,
  location:           MapPin,
  'location-arrow':   Navigation,
  navigation:         Navigation,

  // Time
  calendar:           Calendar,
  'calendar-alt':     CalendarDays,
  'calendar-check':   CalendarDays,
  clock:              Clock,
  time:               Clock,
  history:            Clock,

  // Documents / reports
  'file-text':        FileText,
  'file-alt':         FileText,
  file:               File,
  clipboard:          Clipboard,
  'clipboard-list':   ClipboardList,
  'clipboard-check':  ClipboardCheck,
  list:               List,
  'list-alt':         ListChecks,

  // Charts / analytics
  'bar-chart':        BarChart2,
  'chart-bar':        BarChart2,
  'bar-chart-alt':    BarChart3,
  'line-chart':       LineChart,
  'chart-line':       LineChart,
  'pie-chart':        PieChart,
  'chart-pie':        PieChart,
  'trending-up':      TrendingUp,

  // Organization / company
  building:           Building2,
  'building-2':       Building2,
  briefcase:          Briefcase,
  database:           Database,
  table:              Table2,

  // System / settings
  cog:                Settings,
  gear:               Settings,
  settings:           Settings,
  'sliders':          Settings2,
  'sliders-h':        Settings2,

  // Communications
  bell:               Bell,
  phone:              Phone,
  mobile:             Phone,
  envelope:           Mail,
  mail:               Mail,

  // Status / misc
  info:               Info,
  'info-circle':      Info,
  'exclamation-triangle': AlertTriangle,
  warning:            AlertTriangle,
  'exclamation-circle': AlertCircle,
  check:              CheckCircle2,
  'check-circle':     CheckCircle2,
  tag:                Tag,
  search:             Search,
  activity:           Activity,
  monitor:            Monitor,
  'book-open':        BookOpen,
  book:               BookOpen,
  package:            Package,
  boxes:              Boxes,
}

/* ----------------------------------------------------------------
   Fallback pool — used when ico is null / empty / unrecognized.
   Picked deterministically based on menu.id so the icon is
   stable across re-renders but looks varied across menu items.
   ---------------------------------------------------------------- */
const fallbackPool = [
  LayoutDashboard, Shield, Users, Map, ClipboardList,
  BarChart2, Settings, Calendar, Monitor, FileText,
  Bell, Key, Building2, Activity, Layers,
  Database, Briefcase, TrendingUp, Phone, BookOpen,
]

/**
 * @param {string|null} ico   — raw value from menus.ico
 * @param {number}      menuId — menu.id for deterministic fallback
 * @returns Vue component
 */
export function resolveMenuIcon(ico, menuId = 0) {
  if (ico) {
    // Strip Font Awesome prefixes and normalise
    const key = ico
      .toLowerCase()
      .replace(/^fa\s+fa-/, '')   // "fa fa-shield" → "shield"
      .replace(/^fa-/, '')         // "fa-shield"    → "shield"
      .replace(/\s+/g, '-')        // spaces to hyphens
      .trim()

    if (iconMap[key]) return iconMap[key]
  }

  // Deterministic fallback from the pool
  return fallbackPool[Math.abs(menuId) % fallbackPool.length]
}