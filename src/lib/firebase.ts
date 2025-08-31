
// Import the functions you need from the SDKs you need
import { initializeApp, getApps, getApp, type FirebaseOptions } from "firebase/app";
import { getAuth, connectAuthEmulator } from "firebase/auth";
import { getFirestore, connectFirestoreEmulator } from "firebase/firestore";
import { getStorage } from "firebase/storage";

// Your web app's Firebase configuration
const firebaseConfig: FirebaseOptions = {
  apiKey: process.env.NEXT_PUBLIC_FIREBASE_API_KEY,
  authDomain: process.env.NEXT_PUBLIC_FIREBASE_AUTH_DOMAIN,
  projectId: process.env.NEXT_PUBLIC_FIREBASE_PROJECT_ID,
  storageBucket: process.env.NEXT_PUBLIC_FIREBASE_STORAGE_BUCKET,
  messagingSenderId: process.env.NEXT_PUBLIC_FIREBASE_MESSAGING_SENDER_ID,
  appId: process.env.NEXT_PUBLIC_FIREBASE_APP_ID,
};

// Initialize Firebase
const app = !getApps().length ? initializeApp(firebaseConfig) : getApp();
const auth = getAuth(app);
const db = getFirestore(app);
const storage = getStorage(app);

// We only want to connect to the emulators in a development environment
if (process.env.NODE_ENV === 'development') {
    // Check if we're running in the browser to avoid server-side connection attempts
    if (typeof window !== "undefined") {
        // @ts-ignore
        if (!auth.emulatorConfig) {
            try {
                // connectAuthEmulator(auth, "http://127.0.0.1:9099", { disableWarnings: true });
                // connectFirestoreEmulator(db, "127.0.0.1", 8080);
                // console.log('Successfully connected to Firebase emulators');
            } catch (error) {
                console.error("Error connecting to Firebase emulators:", error);
            }
        }
    }
}

export { app, db, auth, storage };
