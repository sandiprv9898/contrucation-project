<?php

namespace Database\Seeders;

use App\Domain\Localization\Models\Language;
use App\Domain\Localization\Models\TranslationKey;
use App\Domain\Localization\Models\Translation;
use Illuminate\Database\Seeder;

class NavigationTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Adding navigation and company translations...');

        $languages = Language::active()->get()->keyBy('code');
        
        // Define translations for all languages
        $translations = [
            // Navigation translations
            'navigation.dashboard' => [
                'en' => 'Dashboard',
                'es' => 'Panel de Control',
                'fr' => 'Tableau de Bord',
                'de' => 'Dashboard',
                'ar' => 'لوحة التحكم'
            ],
            'navigation.projects' => [
                'en' => 'Projects',
                'es' => 'Proyectos',
                'fr' => 'Projets',
                'de' => 'Projekte',
                'ar' => 'المشاريع'
            ],
            'navigation.tasks' => [
                'en' => 'Tasks',
                'es' => 'Tareas',
                'fr' => 'Tâches',
                'de' => 'Aufgaben',
                'ar' => 'المهام'
            ],
            'navigation.calendar' => [
                'en' => 'Calendar',
                'es' => 'Calendario',
                'fr' => 'Calendrier',
                'de' => 'Kalender',
                'ar' => 'التقويم'
            ],
            'navigation.documents' => [
                'en' => 'Documents',
                'es' => 'Documentos',
                'fr' => 'Documents',
                'de' => 'Dokumente',
                'ar' => 'المستندات'
            ],
            'navigation.settings' => [
                'en' => 'Settings',
                'es' => 'Configuración',
                'fr' => 'Paramètres',
                'de' => 'Einstellungen',
                'ar' => 'الإعدادات'
            ],
            
            // Brand and header
            'navigation.construction' => [
                'en' => 'Construction',
                'es' => 'Construcción',
                'fr' => 'Construction',
                'de' => 'Bau',
                'ar' => 'البناء'
            ],
            'navigation.management_platform' => [
                'en' => 'Management Platform',
                'es' => 'Plataforma de Gestión',
                'fr' => 'Plateforme de Gestion',
                'de' => 'Management-Plattform',
                'ar' => 'منصة الإدارة'
            ],
            
            // Section headers
            'navigation.management' => [
                'en' => 'Management',
                'es' => 'Gestión',
                'fr' => 'Gestion',
                'de' => 'Verwaltung',
                'ar' => 'الإدارة'
            ],
            'navigation.administration' => [
                'en' => 'Administration',
                'es' => 'Administración',
                'fr' => 'Administration',
                'de' => 'Verwaltung',
                'ar' => 'الإدارة'
            ],
            'navigation.reports_analytics' => [
                'en' => 'Reports & Analytics',
                'es' => 'Informes y Análisis',
                'fr' => 'Rapports et Analyses',
                'de' => 'Berichte & Analysen',
                'ar' => 'التقارير والتحليلات'
            ],
            
            // Management navigation items
            'navigation.team_members' => [
                'en' => 'Team Members',
                'es' => 'Miembros del Equipo',
                'fr' => 'Membres de l\'Équipe',
                'de' => 'Teammitglieder',
                'ar' => 'أعضاء الفريق'
            ],
            'navigation.project_settings' => [
                'en' => 'Project Settings',
                'es' => 'Configuración de Proyectos',
                'fr' => 'Paramètres de Projet',
                'de' => 'Projekteinstellungen',
                'ar' => 'إعدادات المشروع'
            ],
            'navigation.task_assignment' => [
                'en' => 'Task Assignment',
                'es' => 'Asignación de Tareas',
                'fr' => 'Attribution de Tâches',
                'de' => 'Aufgabenzuweisung',
                'ar' => 'تخصيص المهام'
            ],
            
            // Admin navigation items
            'navigation.user_management' => [
                'en' => 'User Management',
                'es' => 'Gestión de Usuarios',
                'fr' => 'Gestion des Utilisateurs',
                'de' => 'Benutzerverwaltung',
                'ar' => 'إدارة المستخدمين'
            ],
            'navigation.system_settings' => [
                'en' => 'System Settings',
                'es' => 'Configuración del Sistema',
                'fr' => 'Paramètres du Système',
                'de' => 'Systemeinstellungen',
                'ar' => 'إعدادات النظام'
            ],
            
            // Reports navigation items
            'navigation.project_reports' => [
                'en' => 'Project Reports',
                'es' => 'Informes de Proyectos',
                'fr' => 'Rapports de Projet',
                'de' => 'Projektberichte',
                'ar' => 'تقارير المشاريع'
            ],
            'navigation.time_tracking' => [
                'en' => 'Time Tracking',
                'es' => 'Seguimiento de Tiempo',
                'fr' => 'Suivi du Temps',
                'de' => 'Zeiterfassung',
                'ar' => 'تتبع الوقت'
            ],
            'navigation.cost_analysis' => [
                'en' => 'Cost Analysis',
                'es' => 'Análisis de Costos',
                'fr' => 'Analyse des Coûts',
                'de' => 'Kostenanalyse',
                'ar' => 'تحليل التكاليف'
            ],
            'navigation.performance' => [
                'en' => 'Performance',
                'es' => 'Rendimiento',
                'fr' => 'Performance',
                'de' => 'Leistung',
                'ar' => 'الأداء'
            ],
            
            // Company Settings
            'company.title' => [
                'en' => 'Company Settings',
                'es' => 'Configuración de la Empresa',
                'fr' => 'Paramètres de l\'Entreprise',
                'de' => 'Unternehmenseinstellungen',
                'ar' => 'إعدادات الشركة'
            ],
            'company.subtitle' => [
                'en' => 'Manage your company profile, branding, and showcase',
                'es' => 'Administra el perfil, marca y escaparate de tu empresa',
                'fr' => 'Gérez le profil, la marque et la vitrine de votre entreprise',
                'de' => 'Verwalten Sie Ihr Unternehmensprofil, Branding und Showcase',
                'ar' => 'إدارة ملف الشركة والعلامة التجارية والعرض'
            ],
            'company.tabs.basic' => [
                'en' => 'Basic Info',
                'es' => 'Información Básica',
                'fr' => 'Informations de Base',
                'de' => 'Grundinformationen',
                'ar' => 'المعلومات الأساسية'
            ],
            'company.tabs.branding' => [
                'en' => 'Branding',
                'es' => 'Marca',
                'fr' => 'Image de Marque',
                'de' => 'Branding',
                'ar' => 'العلامة التجارية'
            ],
            'company.tabs.contact' => [
                'en' => 'Contact',
                'es' => 'Contacto',
                'fr' => 'Contact',
                'de' => 'Kontakt',
                'ar' => 'اتصل'
            ],
            'company.tabs.portfolio' => [
                'en' => 'Portfolio',
                'es' => 'Portafolio',
                'fr' => 'Portfolio',
                'de' => 'Portfolio',
                'ar' => 'المحفظة'
            ],
            'company.tabs.legal' => [
                'en' => 'Legal',
                'es' => 'Legal',
                'fr' => 'Juridique',
                'de' => 'Rechtlich',
                'ar' => 'قانوني'
            ],
            
            // Status indicators
            'company.status.profile' => [
                'en' => 'Profile',
                'es' => 'Perfil',
                'fr' => 'Profil',
                'de' => 'Profil',
                'ar' => 'الملف الشخصي'
            ],
            'company.status.branding' => [
                'en' => 'Branding',
                'es' => 'Marca',
                'fr' => 'Image de Marque',
                'de' => 'Branding',
                'ar' => 'العلامة التجارية'
            ],
            'company.status.portfolio' => [
                'en' => 'Portfolio',
                'es' => 'Portafolio',
                'fr' => 'Portfolio',
                'de' => 'Portfolio',
                'ar' => 'المحفظة'
            ],
            'company.status.complete' => [
                'en' => 'Complete',
                'es' => 'Completo',
                'fr' => 'Terminé',
                'de' => 'Vollständig',
                'ar' => 'مكتمل'
            ],
            'company.status.incomplete' => [
                'en' => 'Incomplete',
                'es' => 'Incompleto',
                'fr' => 'Incomplet',
                'de' => 'Unvollständig',
                'ar' => 'غير مكتمل'
            ],
            'company.status.basic' => [
                'en' => 'Basic',
                'es' => 'Básico',
                'fr' => 'Basique',
                'de' => 'Grundlegend',
                'ar' => 'أساسي'
            ],
            'company.status.configured' => [
                'en' => 'Configured',
                'es' => 'Configurado',
                'fr' => 'Configuré',
                'de' => 'Konfiguriert',
                'ar' => 'تم تكوينه'
            ],
            
            // Actions
            'company.actions.save_changes' => [
                'en' => 'Save Changes',
                'es' => 'Guardar Cambios',
                'fr' => 'Enregistrer les Modifications',
                'de' => 'Änderungen Speichern',
                'ar' => 'حفظ التغييرات'
            ],
            'company.actions.saving' => [
                'en' => 'Saving...',
                'es' => 'Guardando...',
                'fr' => 'Enregistrement...',
                'de' => 'Speichern...',
                'ar' => 'جاري الحفظ...'
            ],
            'company.actions.refresh' => [
                'en' => 'Refresh',
                'es' => 'Actualizar',
                'fr' => 'Actualiser',
                'de' => 'Aktualisieren',
                'ar' => 'تحديث'
            ],
            
            // Form fields
            'company.basic.title' => [
                'en' => 'Company Information',
                'es' => 'Información de la Empresa',
                'fr' => 'Informations sur l\'Entreprise',
                'de' => 'Unternehmensinformationen',
                'ar' => 'معلومات الشركة'
            ],
            'company.basic.fields.name.label' => [
                'en' => 'Company Name',
                'es' => 'Nombre de la Empresa',
                'fr' => 'Nom de l\'Entreprise',
                'de' => 'Firmenname',
                'ar' => 'اسم الشركة'
            ],
            'company.basic.fields.legal_name.label' => [
                'en' => 'Legal Name',
                'es' => 'Nombre Legal',
                'fr' => 'Nom Légal',
                'de' => 'Rechtlicher Name',
                'ar' => 'الاسم القانوني'
            ],
            'company.basic.fields.legal_name.placeholder' => [
                'en' => 'Enter legal company name',
                'es' => 'Ingrese el nombre legal de la empresa',
                'fr' => 'Entrez le nom légal de l\'entreprise',
                'de' => 'Rechtlichen Firmennamen eingeben',
                'ar' => 'أدخل الاسم القانوني للشركة'
            ],
            'company.basic.fields.industry_type.label' => [
                'en' => 'Industry Type',
                'es' => 'Tipo de Industria',
                'fr' => 'Type d\'Industrie',
                'de' => 'Branchentyp',
                'ar' => 'نوع الصناعة'
            ],
            'company.basic.fields.company_size.label' => [
                'en' => 'Company Size',
                'es' => 'Tamaño de la Empresa',
                'fr' => 'Taille de l\'Entreprise',
                'de' => 'Unternehmensgröße',
                'ar' => 'حجم الشركة'
            ],
            'company.basic.fields.company_size.placeholder' => [
                'en' => 'Select company size',
                'es' => 'Seleccionar tamaño de empresa',
                'fr' => 'Sélectionner la taille de l\'entreprise',
                'de' => 'Unternehmensgröße auswählen',
                'ar' => 'اختر حجم الشركة'
            ],
            'company.basic.fields.founded_date.label' => [
                'en' => 'Founded Date',
                'es' => 'Fecha de Fundación',
                'fr' => 'Date de Fondation',
                'de' => 'Gründungsdatum',
                'ar' => 'تاريخ التأسيس'
            ],
            'company.basic.fields.website.label' => [
                'en' => 'Website',
                'es' => 'Sitio Web',
                'fr' => 'Site Web',
                'de' => 'Webseite',
                'ar' => 'الموقع الإلكتروني'
            ],
            'company.basic.fields.website.placeholder' => [
                'en' => 'https://company.com',
                'es' => 'https://empresa.com',
                'fr' => 'https://entreprise.com',
                'de' => 'https://unternehmen.com',
                'ar' => 'https://الشركة.com'
            ],
            'company.basic.fields.description.label' => [
                'en' => 'Description',
                'es' => 'Descripción',
                'fr' => 'Description',
                'de' => 'Beschreibung',
                'ar' => 'الوصف'
            ],
            'company.basic.fields.description.placeholder' => [
                'en' => 'Describe your company, its mission, and services...',
                'es' => 'Describe tu empresa, su misión y servicios...',
                'fr' => 'Décrivez votre entreprise, sa mission et ses services...',
                'de' => 'Beschreiben Sie Ihr Unternehmen, seine Mission und Dienstleistungen...',
                'ar' => 'وصف شركتك ورسالتها وخدماتها...'
            ],
            
            // Tax & Legal
            'company.basic.tax_legal.title' => [
                'en' => 'Tax & Legal Information',
                'es' => 'Información Fiscal y Legal',
                'fr' => 'Informations Fiscales et Juridiques',
                'de' => 'Steuer- und Rechtsinformationen',
                'ar' => 'المعلومات الضريبية والقانونية'
            ],
            'company.basic.tax_legal.fields.business_registration.label' => [
                'en' => 'Business Registration Number',
                'es' => 'Número de Registro Comercial',
                'fr' => 'Numéro d\'Enregistrement Commercial',
                'de' => 'Handelsregisternummer',
                'ar' => 'رقم التسجيل التجاري'
            ],
            'company.basic.tax_legal.fields.business_registration.placeholder' => [
                'en' => 'Enter registration number',
                'es' => 'Ingrese el número de registro',
                'fr' => 'Entrez le numéro d\'enregistrement',
                'de' => 'Registernummer eingeben',
                'ar' => 'أدخل رقم التسجيل'
            ],
            'company.basic.tax_legal.fields.tax_identification.label' => [
                'en' => 'Tax Identification Number',
                'es' => 'Número de Identificación Fiscal',
                'fr' => 'Numéro d\'Identification Fiscale',
                'de' => 'Steuerliche Identifikationsnummer',
                'ar' => 'الرقم الضريبي'
            ],
            'company.basic.tax_legal.fields.tax_identification.placeholder' => [
                'en' => '12-3456789',
                'es' => '12-3456789',
                'fr' => '12-3456789',
                'de' => '12-3456789',
                'ar' => '12-3456789'
            ],
            
            // Dashboard translations
            'dashboard.welcome_message' => [
                'en' => 'Welcome to your construction management platform dashboard.',
                'es' => 'Bienvenido al panel de control de tu plataforma de gestión de construcción.',
                'fr' => 'Bienvenue dans le tableau de bord de votre plateforme de gestion de construction.',
                'de' => 'Willkommen im Dashboard Ihrer Bauverwaltungsplattform.',
                'ar' => 'مرحبا بك في لوحة تحكم منصة إدارة البناء الخاصة بك.'
            ],
            'dashboard.total_users' => [
                'en' => 'Total Users',
                'es' => 'Total de Usuarios',
                'fr' => 'Total des Utilisateurs',
                'de' => 'Gesamtbenutzer',
                'ar' => 'إجمالي المستخدمين'
            ],
            'dashboard.active_users' => [
                'en' => 'Active Users',
                'es' => 'Usuarios Activos',
                'fr' => 'Utilisateurs Actifs',
                'de' => 'Aktive Benutzer',
                'ar' => 'المستخدمون النشطون'
            ],
            'dashboard.companies' => [
                'en' => 'Companies',
                'es' => 'Empresas',
                'fr' => 'Entreprises',
                'de' => 'Unternehmen',
                'ar' => 'الشركات'
            ],
            'dashboard.user_roles' => [
                'en' => 'User Roles',
                'es' => 'Roles de Usuario',
                'fr' => 'Rôles d\'Utilisateur',
                'de' => 'Benutzerrollen',
                'ar' => 'أدوار المستخدم'
            ],
            'dashboard.account_info' => [
                'en' => 'Your Account Information',
                'es' => 'Información de tu Cuenta',
                'fr' => 'Informations de votre Compte',
                'de' => 'Ihre Kontoinformationen',
                'ar' => 'معلومات حسابك'
            ],
            'dashboard.full_name' => [
                'en' => 'Full name',
                'es' => 'Nombre completo',
                'fr' => 'Nom complet',
                'de' => 'Vollständiger Name',
                'ar' => 'الاسم الكامل'
            ],
            'dashboard.email_address' => [
                'en' => 'Email address',
                'es' => 'Dirección de correo',
                'fr' => 'Adresse email',
                'de' => 'E-Mail-Adresse',
                'ar' => 'عنوان البريد الإلكتروني'
            ],
            'dashboard.role' => [
                'en' => 'Role',
                'es' => 'Rol',
                'fr' => 'Rôle',
                'de' => 'Rolle',
                'ar' => 'الدور'
            ],
            'dashboard.account_status' => [
                'en' => 'Account status',
                'es' => 'Estado de la cuenta',
                'fr' => 'Statut du compte',
                'de' => 'Kontostatus',
                'ar' => 'حالة الحساب'
            ],
            'dashboard.active' => [
                'en' => 'Active',
                'es' => 'Activo',
                'fr' => 'Actif',
                'de' => 'Aktiv',
                'ar' => 'نشط'
            ],
            'dashboard.quick_actions' => [
                'en' => 'Quick Actions',
                'es' => 'Acciones Rápidas',
                'fr' => 'Actions Rapides',
                'de' => 'Schnellaktionen',
                'ar' => 'الإجراءات السريعة'
            ],
            'dashboard.manage_projects' => [
                'en' => 'Manage construction projects',
                'es' => 'Gestionar proyectos de construcción',
                'fr' => 'Gérer les projets de construction',
                'de' => 'Bauprojekte verwalten',
                'ar' => 'إدارة مشاريع البناء'
            ],
            'dashboard.team' => [
                'en' => 'Team',
                'es' => 'Equipo',
                'fr' => 'Équipe',
                'de' => 'Team',
                'ar' => 'الفريق'
            ],
            'dashboard.manage_team' => [
                'en' => 'Manage team members',
                'es' => 'Gestionar miembros del equipo',
                'fr' => 'Gérer les membres de l\'équipe',
                'de' => 'Teammitglieder verwalten',
                'ar' => 'إدارة أعضاء الفريق'
            ],
            'dashboard.reports' => [
                'en' => 'Reports',
                'es' => 'Informes',
                'fr' => 'Rapports',
                'de' => 'Berichte',
                'ar' => 'التقارير'
            ],
            'dashboard.view_reports' => [
                'en' => 'View project reports',
                'es' => 'Ver informes de proyectos',
                'fr' => 'Voir les rapports de projet',
                'de' => 'Projektberichte anzeigen',
                'ar' => 'عرض تقارير المشروع'
            ],
            'dashboard.recent_activity' => [
                'en' => 'Recent Activity',
                'es' => 'Actividad Reciente',
                'fr' => 'Activité Récente',
                'de' => 'Letzte Aktivität',
                'ar' => 'النشاط الحديث'
            ],
            'dashboard.loading_activity' => [
                'en' => 'Loading recent activity...',
                'es' => 'Cargando actividad reciente...',
                'fr' => 'Chargement de l\'activité récente...',
                'de' => 'Lade letzte Aktivität...',
                'ar' => 'جاري تحميل النشاط الحديث...'
            ],
            'dashboard.no_activity' => [
                'en' => 'No recent activity found.',
                'es' => 'No se encontró actividad reciente.',
                'fr' => 'Aucune activité récente trouvée.',
                'de' => 'Keine letzte Aktivität gefunden.',
                'ar' => 'لم يتم العثور على نشاط حديث.'
            ],
            
            // Common words
            'common.save' => [
                'en' => 'Save',
                'es' => 'Guardar',
                'fr' => 'Enregistrer',
                'de' => 'Speichern',
                'ar' => 'حفظ'
            ],
            'common.cancel' => [
                'en' => 'Cancel',
                'es' => 'Cancelar',
                'fr' => 'Annuler',
                'de' => 'Abbrechen',
                'ar' => 'إلغاء'
            ],
            'common.delete' => [
                'en' => 'Delete',
                'es' => 'Eliminar',
                'fr' => 'Supprimer',
                'de' => 'Löschen',
                'ar' => 'حذف'
            ],
            'common.edit' => [
                'en' => 'Edit',
                'es' => 'Editar',
                'fr' => 'Modifier',
                'de' => 'Bearbeiten',
                'ar' => 'تعديل'
            ],
            'common.loading' => [
                'en' => 'Loading...',
                'es' => 'Cargando...',
                'fr' => 'Chargement...',
                'de' => 'Laden...',
                'ar' => 'جاري التحميل...'
            ]
        ];

        $imported = 0;
        foreach ($translations as $key => $langTranslations) {
            // Create translation key
            $translationKey = TranslationKey::firstOrCreate([
                'namespace' => explode('.', $key)[0],
                'group' => isset(explode('.', $key)[1]) ? explode('.', $key)[1] : null,
                'key' => last(explode('.', $key)),
            ], [
                'description' => "Navigation/Company translation",
                'is_construction_term' => false,
                'requires_localization' => true,
                'type' => 'text',
            ]);

            // Add translations for each language
            foreach ($langTranslations as $langCode => $value) {
                $language = $languages->get($langCode);
                if ($language) {
                    Translation::updateOrCreate([
                        'translation_key_id' => $translationKey->id,
                        'language_id' => $language->id,
                    ], [
                        'value' => $value,
                        'status' => 'approved',
                        'approved_at' => now(),
                    ]);
                    $imported++;
                }
            }
        }

        $this->command->info("Navigation translations imported. Total: {$imported} translations added.");
    }
}