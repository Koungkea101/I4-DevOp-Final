pipeline {
    agent any

    // Poll SCM every 5 minutes
    triggers {
        pollSCM('H/5 * * * *')
    }

    environment {
        // Define environment variables
        APP_ENV = 'testing'
        DB_CONNECTION = 'sqlite'
        DB_DATABASE = ':memory:'
        COMPOSER_ALLOW_SUPERUSER = '1'
        // Email configuration
        CC_EMAIL = 'nannkoungkea00@gmail.com'
    }

    stages {        stage('Checkout') {
            steps {
                echo 'Checking out source code...'

                // Checkout from remote GitHub repository
                git branch: 'main', url: 'https://github.com/Koungkea101/I4-DevOp-Final.git'

                // Get commit information for email notifications
                script {
                    try {
                        env.GIT_COMMIT_AUTHOR = sh(
                            script: "git show -s --pretty=%an",
                            returnStdout: true
                        ).trim()
                        env.GIT_COMMIT_EMAIL = sh(
                            script: "git show -s --pretty=%ae",
                            returnStdout: true
                        ).trim()
                        env.GIT_COMMIT_MESSAGE = sh(
                            script: "git show -s --pretty=%B",
                            returnStdout: true
                        ).trim()
                    } catch (Exception e) {
                        // Fallback values if git commands fail
                        env.GIT_COMMIT_AUTHOR = 'Nann Koungkea'
                        env.GIT_COMMIT_EMAIL = 'nannkoungkea00@gmail.com'
                        env.GIT_COMMIT_MESSAGE = 'Pipeline build from GitHub repository'
                        echo "Warning: Could not get git commit info, using defaults"
                    }
                }

                echo "Commit by: ${env.GIT_COMMIT_AUTHOR} (${env.GIT_COMMIT_EMAIL})"
                echo "Commit message: ${env.GIT_COMMIT_MESSAGE}"
            }
        }

        stage('Install Dependencies') {
            steps {
                echo 'Installing PHP dependencies...'
                sh '''
                    # Install Composer dependencies
                    composer install --no-dev --optimize-autoloader --no-interaction

                    # Copy environment file if it doesn't exist
                    if [ ! -f .env ]; then
                        cp .env.example .env
                    fi

                    # Generate application key
                    php artisan key:generate --ansi
                '''
            }
        }

        stage('Code Quality & Security Checks') {
            parallel {
                stage('PHP Lint') {
                    steps {
                        echo 'Running PHP syntax check...'
                        sh '''
                            find app/ -name "*.php" -exec php -l {} \\;
                            find tests/ -name "*.php" -exec php -l {} \\;
                        '''
                    }
                }

                stage('Laravel Pint (Code Style)') {
                    steps {
                        echo 'Checking code style with Laravel Pint...'
                        sh '''
                            # Install dev dependencies for code style checking
                            composer install --dev --no-interaction
                            ./vendor/bin/pint --test
                        '''
                    }
                }
            }
        }

        stage('Run Tests') {
            steps {
                echo 'Running Laravel tests...'
                sh '''
                    # Ensure dev dependencies are installed for testing
                    composer install --dev --no-interaction

                    # Run database migrations for testing
                    php artisan migrate --force --env=testing

                    # Run PHPUnit tests with coverage
                    ./vendor/bin/phpunit --coverage-text --coverage-clover=coverage.xml

                    # Run Pest tests if available
                    if [ -f ./vendor/bin/pest ]; then
                        ./vendor/bin/pest --coverage
                    fi
                '''
            }
            post {
                always {
                    // Archive test results
                    publishTestResults testResultsPattern: 'tests/_output/*.xml'

                    // Archive coverage reports if available
                    script {
                        if (fileExists('coverage.xml')) {
                            archiveArtifacts artifacts: 'coverage.xml', fingerprint: true
                        }
                    }
                }
            }
        }

        stage('Build Application') {
            steps {
                echo 'Building Laravel application...'
                sh '''
                    # Clear and optimize caches
                    php artisan config:clear
                    php artisan cache:clear
                    php artisan view:clear
                    php artisan route:clear

                    # Optimize for production
                    php artisan config:cache
                    php artisan route:cache
                    php artisan view:cache

                    # Install production dependencies only
                    composer install --no-dev --optimize-autoloader --no-interaction

                    # Build frontend assets if package.json exists
                    if [ -f package.json ]; then
                        if command -v npm &> /dev/null; then
                            npm ci
                            npm run build
                        elif command -v yarn &> /dev/null; then
                            yarn install --frozen-lockfile
                            yarn build
                        fi
                    fi
                '''
            }
        }

        stage('Deploy with Ansible') {
            when {
                anyOf {
                    branch 'main'
                    branch 'master'
                    branch 'production'
                }
            }
            steps {
                echo 'Deploying application using Ansible...'
                dir('ansible') {
                    sh '''
                        # Check if Ansible is installed
                        if ! command -v ansible-playbook &> /dev/null; then
                            echo "Ansible is not installed. Please install Ansible on the Jenkins server."
                            exit 1
                        fi

                        # Run Ansible playbook for deployment
                        ansible-playbook -i inventory.ini ansible-laravel-deployment.yml -v
                    '''
                }
            }
        }
    }

    post {
        success {
            echo 'Pipeline completed successfully!'

            // Send success notification
            emailext (
                subject: "✅ Jenkins Build Success - ${env.JOB_NAME} #${env.BUILD_NUMBER}",
                body: """
                <h2>Build Successful!</h2>
                <p><strong>Project:</strong> ${env.JOB_NAME}</p>
                <p><strong>Build Number:</strong> ${env.BUILD_NUMBER}</p>
                <p><strong>Commit Author:</strong> ${env.GIT_COMMIT_AUTHOR} (${env.GIT_COMMIT_EMAIL})</p>
                <p><strong>Commit Message:</strong> ${env.GIT_COMMIT_MESSAGE}</p>
                <p><strong>Build URL:</strong> <a href="${env.BUILD_URL}">${env.BUILD_URL}</a></p>
                <p>The application has been successfully built, tested, and deployed.</p>
                """,
                mimeType: 'text/html',
                to: "${env.GIT_COMMIT_EMAIL}, ${env.CC_EMAIL}"
            )
        }

        failure {
            echo 'Pipeline failed!'

            // Send failure notification to developer and CC
            emailext (
                subject: "❌ Jenkins Build Failed - ${env.JOB_NAME} #${env.BUILD_NUMBER}",
                body: """
                <h2>Build Failed!</h2>
                <p><strong>Project:</strong> ${env.JOB_NAME}</p>
                <p><strong>Build Number:</strong> ${env.BUILD_NUMBER}</p>
                <p><strong>Commit Author:</strong> ${env.GIT_COMMIT_AUTHOR} (${env.GIT_COMMIT_EMAIL})</p>
                <p><strong>Commit Message:</strong> ${env.GIT_COMMIT_MESSAGE}</p>
                <p><strong>Build URL:</strong> <a href="${env.BUILD_URL}">${env.BUILD_URL}</a></p>
                <p><strong>Console Output:</strong> <a href="${env.BUILD_URL}/console">${env.BUILD_URL}/console</a></p>
                <p style="color: red;">Please check the build logs and fix the issues.</p>
                """,
                mimeType: 'text/html',
                to: "${env.GIT_COMMIT_EMAIL}, ${env.CC_EMAIL}"
            )
        }

        unstable {
            echo 'Pipeline completed with warnings!'

            // Send unstable notification
            emailext (
                subject: "⚠️ Jenkins Build Unstable - ${env.JOB_NAME} #${env.BUILD_NUMBER}",
                body: """
                <h2>Build Unstable!</h2>
                <p><strong>Project:</strong> ${env.JOB_NAME}</p>
                <p><strong>Build Number:</strong> ${env.BUILD_NUMBER}</p>
                <p><strong>Commit Author:</strong> ${env.GIT_COMMIT_AUTHOR} (${env.GIT_COMMIT_EMAIL})</p>
                <p><strong>Commit Message:</strong> ${env.GIT_COMMIT_MESSAGE}</p>
                <p><strong>Build URL:</strong> <a href="${env.BUILD_URL}">${env.BUILD_URL}</a></p>
                <p style="color: orange;">The build completed but with some warnings or test failures.</p>
                """,
                mimeType: 'text/html',
                to: "${env.GIT_COMMIT_EMAIL}, ${env.CC_EMAIL}"
            )
        }

        always {
            // Clean up workspace if needed
            cleanWs(cleanWhenAborted: true, cleanWhenFailure: true, cleanWhenSuccess: true)
        }
    }
}
