import clsx from 'clsx';
import Heading from '@theme/Heading';
import styles from './styles.module.css';
import Link from "@docusaurus/Link";
import packageConfig from "../../../config";

export default function HomepageFeatures() {
  return (
    <section className={styles.features}>
      <div className="container">
        <div className="row">
            <div className={clsx("col col--8")}>
                <Heading as="h1">Introduction</Heading>
                <p>Lmc User Mezzio Authentication provides adapters for Mezzio Authentication</p>
                <Heading as="h2">Support</Heading>
                <ul>
                    <li>File issues at <Link
                        href={'https://github.com/LM-Commons/'+packageConfig.packageName+'/issues'}>
                        github.com/LM-Commons/{packageConfig.packageName}/issues</Link>.
                    </li>
                    <li>Ask questions in the <Link
                        href="https://discord.gg/MSQZQJcS4S">LM-Commons
                        Discord</Link> chat.
                    </li>
                </ul>
            </div>
        </div>
      </div>
    </section>
  );
}
