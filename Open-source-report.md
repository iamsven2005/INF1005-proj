---

# Open Source Case Study

**INF1005 – Web Systems and Technologies**

| Field           | Details                                |
| --------------- | -------------------------------------- |
| Assignment      | Open Source Case Study                 |
| Course          | INF1005 – Web Systems and Technologies |
| Group           | (Group Number)                         |
| Lab Section     | (P1–P8)                                |
| Submission Date | 20 March                               |
| Team Members    | Name (Student ID)                      |

---

# Table of Contents

1. Introduction
2. Chart.js
3. Three.js
4. PHPMailer
5. Slim Framework
6. HTML5 Boilerplate
7. Conclusion
8. References

---

# 1. Introduction

Open-source software plays an important role in modern web development. Many widely used frameworks, libraries, and tools are developed collaboratively by developers around the world through platforms such as GitHub.

The goal of this case study is to examine several open-source web development projects and analyse their features, development activity, strengths, weaknesses, and usefulness for developers. By studying these projects, we can gain a better understanding of how open-source projects are structured and how they can be applied in real-world web development.

The following projects were selected for this case study:

* Chart.js
* Three.js
* PHPMailer
* Slim Framework
* HTML5 Boilerplate

Each project will be analysed based on its background, strengths and weaknesses, and applicability to web development projects.

---

# 2. Chart.js

GitHub Repository:
[https://github.com/chartjs/Chart.js](https://github.com/chartjs/Chart.js)

---

## 2.1 Overview and Background

Chart.js is a widely used open-source JavaScript library designed for creating interactive and responsive data visualizations in web applications. It enables developers to easily generate charts and graphs using the HTML5 canvas element.

The library supports various chart types, including bar charts, line charts, pie charts, doughnut charts, radar charts, and scatter plots. Due to its simple API and lightweight design, Chart.js is commonly used in dashboards, reporting tools, and data analytics applications.

Chart.js is actively maintained by a large open-source community and is frequently updated with new features, improvements, and bug fixes. Its extensive documentation and numerous examples make it accessible for both beginner and experienced developers.

### Repository Information

| Item                  | Details             |
| --------------------- | ------------------- |
| First created         | 2013                |
| Latest commit         | Actively maintained |
| Contributors          | 400+ contributors   |
| Programming languages | JavaScript          |
| Open issues           | 300+                |
| License               | MIT License         |

---

## 2.2 Evaluation

### Strengths

Chart.js offers several advantages that contribute to its popularity among developers.

* **Ease of use** – The library provides a simple and intuitive API, making it easy to integrate into web applications.
* **Multiple chart types** – It supports a wide variety of charts suitable for different data visualization needs.
* **Strong community support** – With hundreds of contributors, the project benefits from continuous improvements and community engagement.
* **Comprehensive documentation** – The official documentation includes tutorials, examples, and configuration guides that help developers quickly get started.
* **Responsive design** – Charts automatically adjust to different screen sizes, making them suitable for modern web interfaces and mobile devices.

### Weaknesses

Despite its advantages, Chart.js also has some limitations.

* **Limited advanced customization** – Compared to more complex visualization libraries such as D3.js, Chart.js offers fewer advanced customization options.
* **Performance with large datasets** – Rendering performance may decrease when handling extremely large datasets because charts are drawn on the canvas element.

### Security Considerations

Chart.js itself does not introduce significant security vulnerabilities, as it mainly functions as a client-side visualization library.

However, developers should ensure that:

* Data used to generate charts is properly validated and sanitized.
* Sensitive data is not exposed directly through client-side visualization.

Following secure coding practices when handling user input or external data sources is important to prevent potential vulnerabilities such as cross-site scripting (XSS).

## 2.3 Usefulness / Applicability

Chart.js is highly useful for web development projects that require data visualization, particularly in dashboards, monitoring systems, and analytics platforms.

For example, it can be used to display:

System performance metrics

Network traffic statistics

User activity trends

Business analytics dashboards

Its ease of integration with modern frameworks such as React, Vue, and Next.js also makes it suitable for contemporary web development environments.

Our team could potentially contribute to the project by:

Reporting bugs or usability issues

Improving documentation

Contributing code enhancements or feature requests

Participating in such open-source projects would also help improve our understanding of collaborative software development.

## 2.4 Repository Activity Analysis (GitHub Insights)

An analysis of the Chart.js GitHub repository shows that the project is actively maintained and supported by a large open-source community.

### Development Activity

The repository receives regular commits from maintainers and contributors. Frequent updates indicate that the project is continuously improved through bug fixes, performance optimizations, and new feature implementations. This level of activity suggests that the library remains relevant and well-supported for modern web development.

### Contributor Community

Chart.js has more than **400 contributors**, which demonstrates strong community involvement. A large contributor base helps ensure that issues are addressed quickly and that the project benefits from diverse expertise.

Community participation typically includes:

* Submitting bug fixes
* Adding new features
* Improving documentation
* Reviewing pull requests

This collaborative model is a key strength of successful open-source projects.

### Issue Tracking

The repository currently has **hundreds of open issues**, which is common for widely used open-source projects. Issues are used by developers and users to report bugs, suggest improvements, or request new features.

Maintainers actively manage these issues by:

* Reviewing bug reports
* Providing fixes or workarounds
* Accepting community pull requests

The presence of an active issue tracker indicates that the development team is engaged with the community and responsive to user feedback.

### Pull Requests

Pull requests are submitted by contributors who propose changes to the codebase. These contributions are reviewed by maintainers before being merged into the main repository.

The pull request process ensures that:

* Code quality is maintained
* New features are properly tested
* Security and stability are preserved

This structured review process helps maintain the reliability and stability of the Chart.js project.

### Overall Repository Health

Based on the number of contributors, ongoing commits, and active issue discussions, Chart.js appears to be a **healthy and actively maintained open-source project**. This makes it a reliable choice for developers who require a stable and widely supported charting library for web applications.



### Conclusion
Chart.js is a well-established and widely adopted open-source JavaScript library for creating interactive and responsive charts in web applications. Its simple API, variety of supported chart types, and strong community support make it a practical tool for developers who need to visualize data efficiently.

The project demonstrates strong repository health, with regular updates, a large number of contributors, and active issue management on GitHub. These factors indicate that the library is actively maintained and continues to evolve with modern web development needs.

While Chart.js may not provide the same level of advanced customization as more complex visualization libraries such as D3.js, its ease of use and lightweight design make it an excellent choice for dashboards, analytics tools, and reporting systems.

Overall, Chart.js is a reliable and useful open-source project that developers can confidently integrate into their applications. Its active community and ongoing development also present opportunities for new contributors to participate in improving the project through bug fixes, feature enhancements, and documentation improvements.

---


# 3. Three.js

GitHub Repository:
[https://github.com/mrdoob/three.js](https://github.com/mrdoob/three.js)

---

## 3.1 Overview and Background

Three.js is an open-source JavaScript library used to make 3D rendering on web applications accessible to everyday developers. Without three.js, creating even a simple rotating cube would require hundreds of lines of code traditionally. However, three.js can reduce this to just a few dozen lines of readable javescript, making it more accessible and significantly reducing the learning curve of developers such that almost anyone can begin making 3D renders. 

Three.js is built on top of WebGL, which is the name of the browser technology that enables 3D graphics. While it is powerful, it requires developers to write complex low-level code to produce even basic results. Three.js reduces complexity into a far more approachable JavaScript interface, making 3D web development accessible without requiring deep knowledge of the underlying graphics pipeline.

The library provides a complete scene-graph architecture, where developers can define a scene, add mesh objects such as geometry and material, position the camera angle, and hand everything to a WebGLRenderer, which handles the GPU communication. 

Beyond that, three.js also provides the following features:
- Rich geometry Library which offers developers with a myriad of different options to integrate into their render
- A comprehensive material system which similar to the point above, also offers developers a plethora of options to choose from when rendering their 3D model.
- A built-in animation mixer that reads keyframe tracks from loaded 3D model files
- helpers for cameras, light, bounding boxes and skeletons.
- loaders for importing standard 3D assets 
- post-processing which features an effect composer pipeline for bloom, depth-of-field, SSAO and other screen space effects
  
Three.js is used across a wide range of industries including web-based games, product configurators such as letting customers customise a car's colour in a browser, data visualisation, architectural walkthroughs and educational simulations.

Three.js was created by Ricardo Cabello, known online as mrdoob, and first published on GitHub on 24th April, 2010

**Repository Information**

| Item                  | Details             |
| --------------------- | ------------------- |
| First created         | 2010                |
| Latest commit         | Actively maintained |
| Contributors          | 2000+ contributors  |
| Programming languages | JavaScript          |
| Open issues           | 432                 |
| License               | MIT License         |

---

## 3.2 Evaluation

### Strengths

Lower entry level for wider accessibility without inhibiting potential or output:
Three.js strikes a balance that is difficuly to achieve as it dramatically lowers the barrier to entry for 3D web graphics while still giving experienced developers full access to low-level WebGL capabilties via shadermaterial and custom render passes. 

Vast support and documentation:
Additionally, with over 100,000 Github stars, three.js has one of the largest communities of any graphics library with extensive documentation for its vast ecosystem. The official forum at discourse.threejs.org is highly active, and there are thousands of community examples, tutorials, courses and even several Youtube Series. To add on to this extensive support system, libraries that are built on top of three.js, such as react three fiber(r3f), also have their own sizeable communities, further adding to the library's accessibility to the community.

Extensive examples repository:
The three.js repository includes hundreds of live examples (viewable at threejs.org/examples) covering nearly every feature of the library. These examples serve as both documentation and starting templates, dramatically reducing development time for developers.

MIT License Possession:
The MIT license is the most permissive widely-used open source license. It allows use in commercial projects, closed-source products, and any other context without requiring the release of source code. This makes three.js suitable for both academic coursework, professional product development or enterprise level applications.

Active and consistent maintenance:
The repository receives new commits almost every day, and new releases are published approximately monthly. The project has maintained this pace for over a decade, which is exceptional longevity for an open source library. This continuity makes it an incredibly reliable dependency as developers can be confident that the library will continue to be maintained and updated for the foreseeable future.

### Weaknesses
Breaking changes between revisions:
Three.js does not follow a traditional semantic versioning, which is the industry standard numbering system for for software releases that follow the format:

Major - a big update likely to break existing code
Minor - new features added, but existing code still works
Patch - small bug fixes, nothing breaks

However,three.js does not follow this convention and instead uses a revision system where every new iteration of the library is denoted as rxxx, where x is the updated number for the newest iteration of the library. As a result of this, developers have no formal way of identifying if a new update is a Major equivalent and risks breaking their existing code, and historically, updates between revision numbers have been known to include breaking changes. As developers have no way of identifying if the new update poses a risk to their codebase, those who do not pin their dependency version may find that their code breaks upon update. This is one of the most frequently cited frustrations in the community 

No built-in physics engine:
Three.js handles rendering only and it has no built-in physics simulation. Developers needing collision detection, rigid body dynamics, or soft body simulation must integrate a separate library such as Cannon.js, Rapier, or Ammo.js. This integration, while documented, adds complexity and can affect performance.

Documentation inconsistency:
Although the official documentation at threejs.org/docs is vast and covers the API, the descriptions are often too concise and lack practical context. Many developers often have to rely on third-party tutorials rather than the official documentations. Additionally, some newer APIs and add-ons have limited or missing documentation, requiring developers to read from the source code directly.

TypeScript support is incomplete:
Three.js is written in JavaScript. Thus, developers looking to work in TypeScript may find it somewhat lacking. Community-maintained TypeScript definitions exist (@types/three), but they occasionally lag behind the library itself, and thus, developers working in typescript may encounter type errors or gaps in covereage when utilising newer features 

### Security Considerations

Three.js is a client-side rendering library, which does not handle authentication, databases or network requets in its core functionality, and as such, it has a naturally limited attack surface. As of early 2026, there are no known critical CVEs directly in the three.js core library that has been documented. However, there have been 2 documented historical instances of vulnerabilities that were directly related to the library. The first is CVE-2020-28496, which was a flaw in three.js's colour parsing logic, where passing an excessively long RGB or HSL colour string would cause uncontrolled resource consumption, potentially freezing the application. This was a coding oversight rather than an external attack, and was resolved in version 0.125.0.

The other was a non-library related vulnerability which actually affected the documentation website instead. CVE-2022-0177 was a Cross-Site Scripting (XSS) vulnerability reported against the three.js documentation site (threejs.org/docs) as it contained a flaw in how it loaded pages. This site utilised an iframe to display the documentation content, and the URL hash for the site was being used to directly determine what was being loaded into that iframe, without any proper validation. This meant that a potential threat actor could craft a malicious URL link that executed arbitrary Javascript code in the browser by effectively piggybacking off threejs.org's trusted domain, allowing threat actors to perform a multitude of malicious actions such as redirecting the user to a fake site, displaying fake content, or acting on behalf of the user without their knowledge. While this is not directly related to the library itself, it is still something worth noting as the documentation is something consistently and very often referred to by developers which effectively puts them at risk of an attack. Fortunately, this vulnerability was discovered and patched before the CVE was officially published, and because it never actually affected the main library but rather the documentation site, it was therefore invalidated and the CVE was withdrawn. 

Additionally, there is another important security-adjacent considerations worth noting:
- Three.js relies on the browser's WebGL instance, which historically has its own security risks such as low-level memory vulnerabilities in the browser's graphics layer, as well as GPU-level attacks that can track users across sessions or leak sensitive information about other processes running on the same device.Although this is more of a browser concern rather than a three.js concern, developers should still be aware of them when working on security-sensitive applications.


## 3.3 Usefulness / Applicability
The teams direction for our INF1005 project is centered around an escape room booking site, which can have great potential when integrated with three.js, given that previews of escape rooms can be done in great detail to showcase what customers can expect when entering the room, a rough overview of the terrain to see if its within their fear appetite, and provide more interactivity and leave a better impression of the business to site visitors, further enticing their commitment to book a session. 

Additionally, given that Three.js is a highly supported and well-documented 3D rendering library, it does show some promise with elevating the project's final submission and overall complexity of the website, on top of the points mentioned before. However, there is one massive drawback, that being that although the library is well-documented with extensive support both officially and by third-parties, it still requires the user to have an understanding of 3D concepts, coordinating systems, cameras, shading and geometry and many more complex subjects. Attempting to integrate 3D rendering of escape rooms, whilst a promising idea, must take into account that the group has no prior experience with working on 3D models and are complete beginners that are completely unfamiliar with 3D graphics. This will consequentually lead to the team having to invest a signifigcant amount of time to learning not only 3D graphics, but also how to utilise the tools that three.js itself provides, which may not be very feasible given the time constraints given by the project deadline. 

Thus, whilst it shows great potential, the team feels that embarking on integrating this library into our projects would be far too ambitious and not practical when accounting for progress of the site's functionality, as well as its due deadline, and hence, will not be integrating the library into our project. 

## 3.4 Realistic opportunities for contribution 
Whilst three.js is a versatile library which can provide many solutions and features for various purposes, our team would not pursue contributing to the core code database at this stage, primarily because the usecase of this library is far out of our scope for this course, and whilst pulling it off would be impressive, it comes at the cost of the rest of the more practical aspects of our website and its not realistic. Hence, as the team will not be utilising this library, we will therefore not be able to make any productive contributions to it as well, neither in its codebase, bug reporting, or documentation improvements.  

---

# 4. PHPMailer

GitHub Repository:
[https://github.com/PHPMailer/PHPMailer](https://github.com/PHPMailer/PHPMailer)

---

## 4.1 Overview and Background

PHPMailer is a popular PHP library used to send emails from web applications. It provides a secure and reliable way to send emails using SMTP, authentication, and encryption.

It is widely used in web applications for sending notifications, password reset emails, and contact form messages.

**Repository Information**

| Item                  | Details             |
| --------------------- | ------------------- |
| First created         | 2001                |
| Latest commit         | Actively maintained |
| Contributors          | 200+ contributors   |
| Programming languages | PHP                 |
| Open issues           | 20+                 |
| License               | LGPL License        |

---

## 4.2 Evaluation

### Strengths

* Easy integration with PHP applications
* Supports SMTP authentication
* Supports secure email protocols such as TLS
* Well-maintained and widely used

### Weaknesses

* Limited functionality outside email handling
* Requires SMTP configuration

### Security Considerations

PHPMailer has had security vulnerabilities in earlier versions. However, these issues have been addressed in newer releases.

Developers must ensure they use the latest version.

---

## 4.3 Usefulness / Applicability

PHPMailer is extremely useful for web applications that need email functionality. It could easily be used in our group project for sending notifications or verification emails.

---

# 5. Slim Framework

GitHub Repository:
[https://github.com/slimphp/Slim](https://github.com/slimphp/Slim)

---

## 5.1 Overview and Background

Slim is a lightweight PHP framework designed for building web applications and APIs. It focuses on simplicity and performance while providing essential tools for routing, middleware, and request handling.

Josh Lockhart first published it to GitHub around 2010, and it has since grown into a well-respected tool in the PHP ecosystem. The project remains actively maintained, with commits as recent as early March 2026.

Over 200 developers have contributed to the framework over its lifetime. Among the most prominent are Josh Lockhart himself, Rob Allen, Andrew Smith, Pierre Bérubé, and a contributor known as "odan".

The codebase is written almost entirely in PHP, and with only around 50+ open issues at any given time, the repository reflects a stable and well-managed project. For a project of this age and usage scale, a modest number of open issues is normal and expected. What matters more is whether issues are being acknowledged and resolved over time. Slim is released under the MIT License.

It is commonly used to build RESTful APIs.

**Repository Information**

| Item                  | Details            |
| --------------------- | ------------------ |
| First created         | 2010               |
| Latest commit         | Active development |
| Contributors          | 300+               |
| Programming languages | PHP                |
| Open issues           | 50+                |
| License               | MIT License        |

---

## 5.2 Evaluation

### Strengths

* Lightweight and fast: Minimal overhead makes it significantly faster than full-stack frameworks like Laravel.
* Interoperability: Natively supports PSR-7 HTTP message interfaces, making it easy to plug in third-party PHP components.
* Simple API routing: Clean and expressive syntax for mapping HTTP methods to specific callbacks.
* Good middleware support: Allows developers to easily run code before and after the core application is invoked.
* Popularity: Its popularity stems from its unopinionated nature, making it the go-to choice for developers who want to build lean microservices without unnecessary bloat.
* Ideal for microservices: Its minimalist architecture and low overhead deliver fast performance and core routing capabilities. Which is everything needed to build efficient, standalone APIs without the excess of a full-stack framework.

### Weaknesses

* Fewer built-in features compared to full frameworks: Lacks built-in ORMs (Object-Relational Mapping) for database management, authentication scaffolds, or front-end templating engines.
* Integration overhead: Requires developers to research and manually integrate additional libraries for complex applications, which can slow down initial project setup.
* Learning curve for architecture: Because it is so flexible, less experienced developers might struggle to structure larger applications effectively.

### Security Considerations

Security depends on how developers implement authentication and validation. Slim itself is secure but requires proper coding practices.

Slim's core has a clean security record, but it is worth being clear about what that means in practice. The framework deliberately ships without built-in protections against common vulnerabilities like Cross-Site Scripting or SQL Injection. That responsibility sits with the developer. Proper data sanitisation, secure session handling, and thorough middleware validation are not optional extras; they are baseline requirements for any Slim application deployed in a real environment.

---

## 5.3 Usefulness / Applicability

Slim would be useful for building APIs in modern web applications.

Slim is well-suited for what the INF1005 group project needs. Standing up a RESTful backend to serve data to the frontend is exactly the kind of task it handles efficiently, and because it does not enforce a particular structure, dividing endpoint responsibilities across the team is a practical and low-friction process. There is no heavy configuration to work through before meaningful development can begin.

On the open-source side, contributing to Slim is a realistic goal given that the codebase is entirely PHP, which maps directly to the team's current skill set. Reasonable starting points would be improving the official documentation, writing unit tests for edge cases, or helping to triage existing open issues. Beyond the technical practice, it is the kind of work that produces a meaningful and verifiable public record of contribution.

---

# 6. HTML5 Boilerplate

GitHub Repository:
[https://github.com/h5bp/html5-boilerplate](https://github.com/h5bp/html5-boilerplate)

---

## 6.1 Overview and Background

HTML5 Boilerplate is an open-source front-end template designed to help developers build fast, robust, and adaptable web applications. It provides a standardized template that incorporates best practices in HTML, CSS, and JavaScript development, allowing developers to avoid repetitive setup tasks and focus on building application-specific features.

It provides optimized default configurations and browser compatibility fixes.

**Repository Information**

| Item                  | Details               |
| --------------------- | --------------------- |
| First created         | 2010                  |
| Latest commit         | Actively maintained   |
| Contributors          | 400+                  |
| Programming languages | HTML, CSS, JavaScript |
| Open issues           | 50+                   |
| License               | MIT License           |

**Contributors**

The project has attracted contributions from a large global community, with hundreds of contributors involved since its inception. It is maintained by a core group of experienced developers under the HTML5 Boilerplate organization, ensuring consistency and quality in updates.

**Programming Languages Used**
  •	HTML
	•	CSS
	•	JavaScript

**License**

HTML5 Boilerplate is distributed under the MIT License, which allows developers to freely use, modify, and distribute the code with minimal restrictions.

---

## 6.2 Evaluation

### Strengths

**Industry Best Practices**
It incorporates widely accepted standards for modern web development like W3C, ensuring high-quality output from the start.

**Performance Optimization**
The template includes features such as efficient asset loading, caching strategies, and optimized file structures, which enhance website performance.

**Cross-Browser Compatibility**
It addresses inconsistencies across different browsers, reducing the need for additional debugging and fixes.

**Security Enhancements**
Includes optional server configurations (e.g., .htaccess) that improve security, such as protection against common vulnerabilities.

**Time Efficiency**
Developers can skip repetitive setup tasks and immediately begin developing core functionality.

### Weaknesses

**Not Beginner-Friendly**
Some components, particularly server configurations, may be difficult for beginners to understand and configure correctly.

**Lack of Built-in Functionality**
Unlike frameworks such as Angular or React, it does not provide reusable components, state management, or routing features.

**Requires Customization**
Developers must build most features from scratch, making it less suitable for rapid application development without additional tools.

### Security Considerations

HTML5 Boilerplate promotes secure development practices by including recommended configurations and guidelines. However: 
- It does not provide backend security features.
- Developers must still implement proper validation, authentication, and protective measures.
- Misconfiguration of provided files ( eg., .htaccess) may introduce vulnerabilities if not handled properly.

---

## 6.3 Usefulness / Applicability

HTML5 Boilerplate would be a valuable asset for our group project as it provides a clean and optimized foundation for development. By using it, we can ensure that our project adheres to best practices in performance, structure, and compatibility from the outset. This allows us to focus more on implementing features rather than setting up the development environment.

While contributing to HTML5 Boilerplate requires a strong understanding of web standards and best practices, our team may consider contributing in the future as our skills improve. Potential contributions could include improving documentation, fixing minor issues, or suggesting enhancements based on our development experience.

---

# 7. Conclusion

HTML5 Boilerplate is a highly practical and efficient tool for modern web development. Although it is not a full-featured framework, its strength lies in providing a robust and optimized foundation for projects. Its emphasis on performance, compatibility, and best practices makes it especially valuable for developers who want greater control over their projects while maintaining high standards.

Overall, HTML5 Boilerplate remains a relevant and widely used tool, particularly for developers seeking a lightweight yet powerful foundation for building web applications.
Key findings include:

* Chart.js simplifies data visualization for web dashboards.
* Three.js enables powerful 3D graphics in web browsers.
* PHPMailer provides reliable email functionality for PHP applications.
* Slim Framework allows developers to build lightweight APIs.
* HTML5 Boilerplate helps developers start web projects using best practices.

Overall, these open-source projects demonstrate the importance of community collaboration and provide valuable tools for developers. Many of them could be used in our future web development projects.

---

# 8. References

GitHub repositories:

* [https://github.com/chartjs/Chart.js](https://github.com/chartjs/Chart.js)
* [https://github.com/mrdoob/three.js](https://github.com/mrdoob/three.js)
* [https://github.com/PHPMailer/PHPMailer](https://github.com/PHPMailer/PHPMailer)
* [https://github.com/slimphp/Slim](https://github.com/slimphp/Slim)
* [https://github.com/h5bp/html5-boilerplate](https://github.com/h5bp/html5-boilerplate)

---

